<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        // Check subscription
        if (!$this->hasActiveSubscription($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Active subscription required'
            ], 403);
        }

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            ['id' => $request->conversation_id, 'user_id' => $user->id],
            [
                'title' => $this->generateTitle($request->message),
                'last_message_at' => now(),
            ]
        );

        // Save user message
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'content' => $request->message,
            'is_user' => true,
            'message_type' => Message::TYPE_TEXT,
        ]);

        // Generate AI response
        try {
            $aiResponse = $this->aiService->generateResponse($request->message, $conversation);
            
            // Save AI message
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $aiResponse,
                'is_user' => false,
                'message_type' => Message::TYPE_TEXT,
            ]);

            // Update conversation
            $conversation->update(['last_message_at' => now()]);

            return response()->json([
                'success' => true,
                'response' => $aiResponse,
                'conversation_id' => $conversation->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate response'
            ], 500);
        }
    }

    public function analyzeImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        // Check subscription
        if (!$this->hasActiveSubscription($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Active subscription required'
            ], 403);
        }

        // Find or create conversation
        $conversation = Conversation::firstOrCreate(
            ['id' => $request->conversation_id, 'user_id' => $user->id],
            [
                'title' => 'Image Analysis',
                'last_message_at' => now(),
            ]
        );

        // Store image
        $imagePath = $request->file('image')->store('medical_images', 'public');

        // Save user message with image
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'content' => 'Sent an image for analysis',
            'is_user' => true,
            'message_type' => Message::TYPE_IMAGE,
            'image_path' => $imagePath,
        ]);

        // Analyze image with AI
        try {
            $analysis = $this->aiService->analyzeImage($imagePath);
            
            // Save AI analysis
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $analysis,
                'is_user' => false,
                'message_type' => Message::TYPE_TEXT,
            ]);

            // Update conversation
            $conversation->update(['last_message_at' => now()]);

            return response()->json([
                'success' => true,
                'analysis' => $analysis,
                'conversation_id' => $conversation->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze image'
            ], 500);
        }
    }

    private function hasActiveSubscription($user)
    {
        return $user->subscription && 
               $user->subscription->is_active && 
               !$user->subscription->is_expired;
    }

    private function generateTitle($message)
    {
        return strlen($message) > 30 ? substr($message, 0, 30) . '...' : $message;
    }
}