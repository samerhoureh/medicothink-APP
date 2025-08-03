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
            
            // Check token usage
            if (!$user->canUseTokens()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token limit exceeded. Please upgrade your subscription.',
                ], 403);
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
                'data' => [
                    'conversation_id' => $conversation->id,
                    'user_message' => [
                        'id' => $userMessage->id,
                        'content' => $userMessage->content,
                        'is_from_user' => true,
                        'created_at' => $userMessage->created_at,
                    ],
                    'ai_message' => [
                        'id' => $aiMessage->id,
                        'content' => $aiMessage->content,
                        'is_from_user' => false,
                        'created_at' => $aiMessage->created_at,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
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
            
            // Check image usage
            if (!$user->canUseImages()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image analysis limit exceeded. Please upgrade your subscription.',
                ], 403);
            }

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
                    'title' => 'تحليل صورة طبية - ' . now()->format('Y-m-d H:i'),
                ]);
            }

            // Save user message with image
            $userMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => $request->question ?: 'يرجى تحليل هذه الصورة الطبية',
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
                'data' => [
                    'conversation_id' => $conversation->id,
                    'user_message' => [
                        'id' => $userMessage->id,
                        'content' => $userMessage->content,
                        'is_from_user' => true,
                        'image_url' => asset('storage/' . $imagePath),
                        'created_at' => $userMessage->created_at,
                    ],
                    'analysis' => [
                        'id' => $aiMessage->id,
                        'content' => $analysis,
                        'is_from_user' => false,
                        'created_at' => $aiMessage->created_at,
                    ],
                ],
            ]);

        } catch (\Exception $e) {
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
            
            // Check image usage
            if (!$user->canUseImages()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image generation limit exceeded. Please upgrade your subscription.',
                ], 403);
            }

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
                    'title' => 'توليد صورة - ' . now()->format('Y-m-d H:i'),
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
                'content' => 'تم توليد الصورة بناءً على طلبك',
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
                'data' => [
                    'conversation_id' => $conversation->id,
                    'image_url' => asset('storage/' . $imagePath),
                    'message_id' => $aiMessage->id,
                ],
            ]);

        } catch (\Exception $e) {
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
            
            // Check video usage
            if (!$user->canUseVideos()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video generation limit exceeded. Please upgrade your subscription.',
                ], 403);
            }

            // Generate video (placeholder implementation)
            $videoPath = $this->aiService->generateVideo($request->prompt);

            if (!$videoPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Video generation is not available yet',
                ], 501);
            }

            // Increment video usage
            $user->incrementUsage('videos');

            return response()->json([
                'success' => true,
                'data' => [
                    'video_url' => asset('storage/' . $videoPath),
                ],
            ]);

        } catch (\Exception $e) {
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
            
            // Check token usage
            if (!$user->canUseTokens()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token limit exceeded. Please upgrade your subscription.',
                ], 403);
            }

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
                    'title' => 'بطاقات تعليمية - ' . $request->topic,
                ]);
            }

            // Save user request
            $userMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => "إنشاء بطاقات تعليمية حول: {$request->topic}",
                'is_from_user' => true,
                'message_type' => 'text',
            ]);

            // Save flashcards
            $aiMessage = Message::create([
                'conversation_id' => $conversation->id,
                'content' => 'تم إنشاء البطاقات التعليمية',
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
                'data' => [
                    'conversation_id' => $conversation->id,
                    'flashcards' => $flashcards,
                    'message_id' => $aiMessage->id,
                ],
            ]);

        } catch (\Exception $e) {
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