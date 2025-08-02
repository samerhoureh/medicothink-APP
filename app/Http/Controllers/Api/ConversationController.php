<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\AiService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        $user = auth()->user();
        
        $conversations = Conversation::where('user_id', $user->id)
            ->with(['messages' => function($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'conversations' => $conversations
        ]);
    }

    public function show($id)
    {
        $user = auth()->user();
        
        $conversation = Conversation::where('user_id', $user->id)
            ->where('id', $id)
            ->with('messages')
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'conversation' => $conversation
        ]);
    }

    public function archive(Request $request)
    {
        $user = auth()->user();
        
        $conversation = Conversation::where('user_id', $user->id)
            ->where('id', $request->conversation_id)
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

    public function unarchive(Request $request)
    {
        $user = auth()->user();
        
        $conversation = Conversation::where('user_id', $user->id)
            ->where('id', $request->conversation_id)
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

    public function destroy($id)
    {
        $user = auth()->user();
        
        $conversation = Conversation::where('user_id', $user->id)
            ->where('id', $id)
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

    public function summary($id)
    {
        $user = auth()->user();
        
        $conversation = Conversation::where('user_id', $user->id)
            ->where('id', $id)
            ->with(['messages', 'summary'])
            ->first();

        if (!$conversation) {
            return response()->json([
                'success' => false,
                'message' => 'Conversation not found'
            ], 404);
        }

        // Generate summary if not exists
        if (!$conversation->summary) {
            try {
                $summaryData = $this->aiService->generateSummary($conversation);
                $conversation->summary()->create($summaryData);
                $conversation->load('summary');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate summary'
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'summary' => $conversation->summary
        ]);
    }
}