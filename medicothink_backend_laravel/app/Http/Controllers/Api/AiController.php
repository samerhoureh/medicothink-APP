<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiService;
use App\Models\Conversation;
use App\Models\Message;
use App\Http\Requests\ChatRequest;
use App\Http\Requests\AnalyzeImageRequest;
use App\Http\Requests\GenerateImageRequest;
use App\Http\Requests\GenerateVideoRequest;
use App\Http\Requests\GenerateFlashcardsRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AiController extends Controller
{
    protected $aiService;

    public function __construct(AiService $aiService)
    {
        $this->aiService = $aiService;
        $this->middleware('auth:sanctum');
        $this->middleware('subscription.check');
    }

    public function chat(ChatRequest $request)
    {
        try {
            $user = $request->user();

            // Get or create conversation
            $conversation = null;
            if ($request->conversation_id) {
                $conversation = Conversation::where('id', $request->conversation_id)
                    ->where('user_id', $user->id)
                    ->first();
            }

            if (!$conversation) {
                $conversation = Conversation::create([
                    'user_id' => $user->id,
                    'title' => $this->generateConversationTitle($request->message),
                ]);
            }

            // Get conversation context
            $context = $conversation->messages()
                ->latest()
                ->take(10)
                ->get()
                ->reverse()
                ->map(function ($message) {
                    return [
                        'content' => $message->content,
                        'is_from_user' => $message->is_from_user,
                    ];
                })
                ->toArray();

            // Save user message
            $userMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $request->message,
                'is_from_user' => true,
                'message_type' => 'text',
            ]);

            // Get AI response
            $aiResponse = $this->aiService->chat($request->message, $context);

            // Save AI message
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $aiResponse,
                'is_from_user' => false,
                'message_type' => 'text',
            ]);

            // Update conversation
            $conversation->updateLastMessageTime();

            // Increment token usage
            $user->incrementUsage('tokens');

            return response()->json([
                'success' => true,
                'message' => 'Chat response generated successfully',
                'data' => [
                    'conversation_id' => $conversation->id,
                    'user_message' => [
                        'id' => $userMessage->id,
                        'content' => $userMessage->content,
                        'is_from_user' => true,
                        'message_type' => 'text',
                        'created_at' => $userMessage->created_at,
                    ],
                    'ai_message' => [
                        'id' => $aiMessage->id,
                        'content' => $aiMessage->content,
                        'is_from_user' => false,
                        'message_type' => 'text',
                        'created_at' => $aiMessage->created_at,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Chat Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Chat request failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function analyzeImage(AnalyzeImageRequest $request)
    {
        try {
            $user = $request->user();

            // Store uploaded image
            $imagePath = $request->file('image')->store('medical_images', 'public');

            // Get or create conversation
            $conversation = null;
            if ($request->conversation_id) {
                $conversation = Conversation::where('id', $request->conversation_id)
                    ->where('user_id', $user->id)
                    ->first();
            }

            if (!$conversation) {
                $conversation = Conversation::create([
                    'user_id' => $user->id,
                    'title' => 'Medical Image Analysis - ' . now()->format('Y-m-d H:i'),
                ]);
            }

            // Save user message with image
            $userMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $request->question ?: 'Please analyze this medical image',
                'is_from_user' => true,
                'message_type' => 'image',
                'image_path' => $imagePath,
            ]);

            // Analyze image
            $analysis = $this->aiService->analyzeImage($imagePath, $request->question);

            // Save AI response
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $analysis,
                'is_from_user' => false,
                'message_type' => 'text',
            ]);

            // Update conversation
            $conversation->updateLastMessageTime();

            // Increment image usage
            $user->incrementUsage('images');

            return response()->json([
                'success' => true,
                'message' => 'Image analysis completed successfully',
                'data' => [
                    'conversation_id' => $conversation->id,
                    'user_message' => [
                        'id' => $userMessage->id,
                        'content' => $userMessage->content,
                        'is_from_user' => true,
                        'message_type' => 'image',
                        'image_url' => asset('storage/' . $imagePath),
                        'created_at' => $userMessage->created_at,
                    ],
                    'analysis' => [
                        'id' => $aiMessage->id,
                        'content' => $analysis,
                        'is_from_user' => false,
                        'message_type' => 'text',
                        'created_at' => $aiMessage->created_at,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Image Analysis Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Image analysis failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateImage(GenerateImageRequest $request)
    {
        try {
            $user = $request->user();

            // Generate image
            $imagePath = $this->aiService->generateImage($request->prompt);

            if (!$imagePath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image generation failed',
                ], 500);
            }

            // Get or create conversation
            $conversation = null;
            if ($request->conversation_id) {
                $conversation = Conversation::where('id', $request->conversation_id)
                    ->where('user_id', $user->id)
                    ->first();
            }

            if (!$conversation) {
                $conversation = Conversation::create([
                    'user_id' => $user->id,
                    'title' => 'Image Generation - ' . now()->format('Y-m-d H:i'),
                ]);
            }

            // Save user request
            $userMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $request->prompt,
                'is_from_user' => true,
                'message_type' => 'text',
            ]);

            // Save generated image
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => 'Image generated based on your request',
                'is_from_user' => false,
                'message_type' => 'image',
                'image_path' => $imagePath,
            ]);

            // Update conversation
            $conversation->updateLastMessageTime();

            // Increment image usage
            $user->incrementUsage('images');

            return response()->json([
                'success' => true,
                'message' => 'Image generated successfully',
                'data' => [
                    'conversation_id' => $conversation->id,
                    'image_url' => asset('storage/' . $imagePath),
                    'message_id' => $aiMessage->id,
                    'prompt' => $request->prompt,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Image Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Image generation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateVideo(GenerateVideoRequest $request)
    {
        try {
            $user = $request->user();

            // Generate video (placeholder implementation)
            $videoPath = $this->aiService->generateVideo($request->prompt);

            if (!$videoPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video generation is not available yet. This feature is coming soon.',
                ], 501);
            }

            // Increment video usage
            $user->incrementUsage('videos');

            return response()->json([
                'success' => true,
                'message' => 'Video generated successfully',
                'data' => [
                    'video_url' => asset('storage/' . $videoPath),
                    'prompt' => $request->prompt,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Video Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Video generation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function generateFlashcards(GenerateFlashcardsRequest $request)
    {
        try {
            $user = $request->user();

            // Generate flashcards
            $flashcards = $this->aiService->generateFlashcards(
                $request->topic,
                $request->count ?? 5
            );

            // Get or create conversation
            $conversation = null;
            if ($request->conversation_id) {
                $conversation = Conversation::where('id', $request->conversation_id)
                    ->where('user_id', $user->id)
                    ->first();
            }

            if (!$conversation) {
                $conversation = Conversation::create([
                    'user_id' => $user->id,
                    'title' => 'Flashcards - ' . $request->topic,
                ]);
            }

            // Save user request
            $userMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => "Generate flashcards about: {$request->topic}",
                'is_from_user' => true,
                'message_type' => 'text',
            ]);

            // Save flashcards
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => 'Flashcards have been generated',
                'is_from_user' => false,
                'message_type' => 'flashcards',
                'flashcards' => $flashcards,
            ]);

            // Update conversation
            $conversation->updateLastMessageTime();

            // Increment token usage
            $user->incrementUsage('tokens');

            return response()->json([
                'success' => true,
                'message' => 'Flashcards generated successfully',
                'data' => [
                    'conversation_id' => $conversation->id,
                    'flashcards' => $flashcards,
                    'message_id' => $aiMessage->id,
                    'topic' => $request->topic,
                    'count' => count($flashcards),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Flashcards Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Flashcards generation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function generateConversationTitle($message)
    {
        $title = substr($message, 0, 50);
        if (strlen($message) > 50) {
            $title .= '...';
        }
        return $title;
    }
}