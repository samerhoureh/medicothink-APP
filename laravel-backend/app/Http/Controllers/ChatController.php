<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\ConversationSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\OpenAIService;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;

class ChatController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
        $this->middleware('auth:api');
        $this->middleware('subscription.active');
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $conversationId = $request->conversation_id;

        // Find or create conversation
        $conversation = Conversation::where('id', $conversationId)
                                  ->where('user_id', $user->id)
                                  ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'id' => $conversationId,
                'user_id' => $user->id,
                'title' => $this->generateTitle($request->message),
                'last_message_at' => now(),
            ]);
        }

        // Save user message
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'content' => $request->message,
            'is_from_user' => true,
            'message_type' => Message::TYPE_TEXT,
        ]);

        // Generate AI response
        try {
            $aiResponse = $this->openAIService->generateResponse(
                $request->message,
                $conversation->messages()->orderBy('created_at')->get()
            );

            // Save AI message
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $aiResponse,
                'is_from_user' => false,
                'message_type' => Message::TYPE_TEXT,
            ]);

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
                'message_id' => $aiMessage->id,
                'conversation_id' => $conversation->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate AI response',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function analyzeImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $conversationId = $request->conversation_id;

        // Find or create conversation
        $conversation = Conversation::where('id', $conversationId)
                                  ->where('user_id', $user->id)
                                  ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'id' => $conversationId,
                'user_id' => $user->id,
                'title' => 'Image Analysis',
                'last_message_at' => now(),
            ]);
        }

        // Store image temporarily
        $imagePath = $request->file('image')->store('temp_images', 'public');

        // Save user message
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'content' => 'Sent an image for analysis',
            'is_from_user' => true,
            'message_type' => Message::TYPE_IMAGE,
            'image_path' => $imagePath,
        ]);

        try {
            // Analyze image with AI
            $analysis = $this->openAIService->analyzeImage(storage_path('app/public/' . $imagePath));

            // Save AI response
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $analysis,
                'is_from_user' => false,
                'message_type' => Message::TYPE_TEXT,
            ]);

            // Clean up temporary image
            \Storage::disk('public')->delete($imagePath);

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
                'message_id' => $aiMessage->id,
                'conversation_id' => $conversation->id
            ]);

        } catch (\Exception $e) {
            // Clean up temporary image
            \Storage::disk('public')->delete($imagePath);

            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getConversations(Request $request)
    {
        $user = auth()->user();
        
        $conversations = Conversation::where('user_id', $user->id)
                                   ->with(['latestMessage'])
                                   ->recent()
                                   ->paginate(20);

        return response()->json([
            'success' => true,
            'conversations' => ConversationResource::collection($conversations->items()),
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ]
        ]);
    }

    public function getConversation(Request $request, $conversationId)
    {
        $user = auth()->user();
        
        $conversation = Conversation::where('id', $conversationId)
                                  ->where('user_id', $user->id)
                                  ->with(['messages'])
                                  ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'conversation' => new ConversationResource($conversation),
            'messages' => MessageResource::collection($conversation->messages)
        ]);
    }

    public function archiveConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $conversation = Conversation::where('id', $request->conversation_id)
                                  ->where('user_id', $user->id)
                                  ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        $conversation->archive();

        return response()->json([
            'success' => true,
            'message' => 'Conversation archived successfully'
        ]);
    }

    public function unarchiveConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $conversation = Conversation::where('id', $request->conversation_id)
                                  ->where('user_id', $user->id)
                                  ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        $conversation->unarchive();

        return response()->json([
            'success' => true,
            'message' => 'Conversation unarchived successfully'
        ]);
    }

    public function deleteConversation(Request $request, $conversationId)
    {
        $user = auth()->user();
        $conversation = Conversation::where('id', $conversationId)
                                  ->where('user_id', $user->id)
                                  ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully'
        ]);
    }

    public function getConversationSummary(Request $request, $conversationId)
    {
        $user = auth()->user();
        $conversation = Conversation::where('id', $conversationId)
                                  ->where('user_id', $user->id)
                                  ->with(['messages', 'conversationSummary'])
                                  ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        // Generate summary if not exists
        if (!$conversation->conversationSummary) {
            try {
                $summary = $this->openAIService->generateConversationSummary($conversation->messages);
                
                $conversationSummary = ConversationSummary::create([
                    'conversation_id' => $conversation->id,
                    'title' => $conversation->title,
                    'summary' => $summary['summary'],
                    'key_points' => $summary['key_points'],
                    'recommendations' => $summary['recommendations'],
                    'diagnosis' => $summary['diagnosis'],
                    'symptoms' => $summary['symptoms'],
                    'treatment' => $summary['treatment'],
                    'generated_at' => now(),
                ]);

                $conversation->load('conversationSummary');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate summary',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'id' => $conversation->conversationSummary->id,
            'conversation_id' => $conversation->id,
            'title' => $conversation->conversationSummary->title,
            'summary' => $conversation->conversationSummary->summary,
            'key_points' => $conversation->conversationSummary->key_points,
            'recommendations' => $conversation->conversationSummary->recommendations,
            'diagnosis' => $conversation->conversationSummary->diagnosis,
            'symptoms' => $conversation->conversationSummary->symptoms,
            'treatment' => $conversation->conversationSummary->treatment,
            'created_at' => $conversation->conversationSummary->generated_at,
        ]);
    }

    private function generateTitle($message)
    {
        return strlen($message) > 30 ? substr($message, 0, 30) . '...' : $message;
    }
}