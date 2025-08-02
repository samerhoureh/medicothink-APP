<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\EnhancedAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EnhancedChatController extends Controller
{
    protected $aiService;

    public function __construct(EnhancedAiService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function sendTextMessage(Request $request)
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
        
        if (!$this->hasActiveSubscription($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Active subscription required'
            ], 403);
        }

        $conversation = $this->getOrCreateConversation($request->conversation_id, $user->id, $request->message);

        // Save user message
        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'content' => $request->message,
            'is_user' => true,
            'message_type' => Message::TYPE_TEXT,
        ]);

        try {
            $aiResponse = $this->aiService->generateTextResponse($request->message, $conversation);
            
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $aiResponse,
                'is_user' => false,
                'message_type' => Message::TYPE_TEXT,
            ]);

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
            'prompt' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        if (!$this->hasActiveSubscription($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Active subscription required'
            ], 403);
        }

        $conversation = $this->getOrCreateConversation($request->conversation_id, $user->id, 'Image Analysis');

        $imagePath = $request->file('image')->store('medical_images', 'public');

        $userMessage = Message::create([
            'conversation_id' => $conversation->id,
            'content' => 'Sent an image for analysis',
            'is_user' => true,
            'message_type' => Message::TYPE_IMAGE,
            'image_path' => $imagePath,
        ]);

        try {
            $analysis = $this->aiService->analyzeImage($imagePath, $request->prompt);
            
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $analysis,
                'is_user' => false,
                'message_type' => Message::TYPE_TEXT,
            ]);

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

    public function textToSpeech(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:5000',
            'voice_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        if (!$this->hasActiveSubscription($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Active subscription required'
            ], 403);
        }

        try {
            $audioPath = $this->aiService->textToSpeech(
                $request->text,
                $request->voice_id ?? 'default'
            );

            return response()->json([
                'success' => true,
                'audio_url' => asset('storage/' . $audioPath),
                'audio_path' => $audioPath,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate speech'
            ], 500);
        }
    }

    public function speechToText(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'audio' => 'required|file|mimes:mp3,wav,m4a,ogg|max:25600', // 25MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        if (!$this->hasActiveSubscription($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Active subscription required'
            ], 403);
        }

        $audioPath = $request->file('audio')->store('audio_uploads', 'public');

        try {
            $transcription = $this->aiService->speechToText($audioPath);

            return response()->json([
                'success' => true,
                'transcription' => $transcription,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to transcribe audio'
            ], 500);
        }
    }

    public function generateImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:1000',
            'size' => 'nullable|string|in:256x256,512x512,1024x1024',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        if (!$this->hasActiveSubscription($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Active subscription required'
            ], 403);
        }

        try {
            $imagePath = $this->aiService->generateImage(
                $request->prompt,
                $request->size ?? '1024x1024'
            );

            return response()->json([
                'success' => true,
                'image_url' => asset('storage/' . $imagePath),
                'image_path' => $imagePath,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate image'
            ], 500);
        }
    }

    public function generateVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:1000',
            'duration' => 'nullable|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        if (!$this->hasActiveSubscription($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Active subscription required'
            ], 403);
        }

        try {
            $videoPath = $this->aiService->generateVideo(
                $request->prompt,
                $request->duration ?? 5
            );

            return response()->json([
                'success' => true,
                'video_url' => asset('storage/' . $videoPath),
                'video_path' => $videoPath,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate video'
            ], 500);
        }
    }

    private function getOrCreateConversation($conversationId, $userId, $title)
    {
        return Conversation::firstOrCreate(
            ['id' => $conversationId, 'user_id' => $userId],
            [
                'title' => $this->generateTitle($title),
                'last_message_at' => now(),
            ]
        );
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