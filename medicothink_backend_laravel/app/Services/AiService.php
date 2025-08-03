<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AiService
{
    protected $openaiApiKey;
    protected $geminiApiKey;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
        $this->geminiApiKey = config('services.gemini.api_key');
    }

    public function chat(string $message, array $context = [])
    {
        try {
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are MedicoThink, an AI medical assistant. Provide helpful medical information while always recommending users consult healthcare professionals for serious concerns. Respond in Arabic or English based on the user\'s language.'
                ]
            ];

            // Add context messages
            foreach ($context as $msg) {
                $messages[] = [
                    'role' => $msg['is_from_user'] ? 'user' : 'assistant',
                    'content' => $msg['content']
                ];
            }

            // Add current message
            $messages[] = [
                'role' => 'user',
                'content' => $message
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'];
            }

            throw new \Exception('OpenAI API request failed');

        } catch (\Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            return 'عذراً، أواجه صعوبة تقنية حالياً. يرجى المحاولة مرة أخرى لاحقاً.';
        }
    }

    public function analyzeImage(string $imagePath, string $question = null)
    {
        try {
            $imageData = base64_encode(Storage::get($imagePath));
            
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are MedicoThink, an AI medical assistant specialized in medical image analysis. Analyze medical images and provide insights while always recommending professional medical consultation. Respond in Arabic or English based on the user\'s language.'
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $question ?: 'Please analyze this medical image and provide insights.'
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:image/jpeg;base64,{$imageData}"
                            ]
                        ]
                    ]
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4-vision-preview',
                'messages' => $messages,
                'max_tokens' => 1000,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'];
            }

            throw new \Exception('OpenAI Vision API request failed');

        } catch (\Exception $e) {
            Log::error('Image Analysis Error: ' . $e->getMessage());
            return 'عذراً، لا يمكنني تحليل هذه الصورة حالياً. يرجى المحاولة مرة أخرى لاحقاً.';
        }
    }

    public function generateImage(string $prompt)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/images/generations', [
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'size' => '1024x1024',
                'quality' => 'standard',
                'n' => 1,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $imageUrl = $data['data'][0]['url'];
                
                // Download and store the image
                $imageContent = Http::get($imageUrl)->body();
                $filename = 'generated_images/' . uniqid() . '.png';
                Storage::put($filename, $imageContent);
                
                return $filename;
            }

            throw new \Exception('OpenAI Image Generation API request failed');

        } catch (\Exception $e) {
            Log::error('Image Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateVideo(string $prompt)
    {
        // This would integrate with a video generation service
        // For now, return a placeholder
        try {
            // Placeholder for video generation logic
            Log::info('Video generation requested: ' . $prompt);
            
            // Return a sample video path or null if not implemented
            return null;
            
        } catch (\Exception $e) {
            Log::error('Video Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateFlashcards(string $topic, int $count = 5)
    {
        try {
            $prompt = "Create {$count} medical flashcards about '{$topic}'. Return as JSON array with 'question' and 'answer' fields. Focus on key medical concepts, symptoms, treatments, or diagnostic criteria. Respond in Arabic or English based on the topic language.";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a medical education assistant. Create educational flashcards in JSON format.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'max_tokens' => 1500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'];
                
                // Try to parse JSON from the response
                $flashcards = json_decode($content, true);
                
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $flashcards;
                }
                
                // If JSON parsing fails, return a default structure
                return [
                    [
                        'question' => 'What is ' . $topic . '?',
                        'answer' => $content
                    ]
                ];
            }

            throw new \Exception('OpenAI API request failed');

        } catch (\Exception $e) {
            Log::error('Flashcards Generation Error: ' . $e->getMessage());
            return [];
        }
    }
}