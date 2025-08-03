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

    protected function detectLanguage(string $text): string
    {
        // Simple Arabic detection
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'ar';
        }
        return 'en';
    }

    protected function getSystemPrompt(string $language = 'en'): string
    {
        if ($language === 'ar') {
            return 'أنت MedicoThink، مساعد ذكي طبي متقدم. قدم معلومات طبية مفيدة ودقيقة باللغة العربية مع التأكيد دائماً على ضرورة استشارة المختصين الطبيين للحالات الجدية. كن مفيداً ومتفهماً ومهنياً في ردودك.';
        }
        
        return 'You are MedicoThink, an advanced AI medical assistant. Provide helpful, accurate medical information in English while always recommending users consult healthcare professionals for serious concerns. Be helpful, understanding, and professional in your responses.';
    }

    public function chat(string $message, array $context = [])
    {
        try {
            $language = $this->detectLanguage($message);
            $systemPrompt = $this->getSystemPrompt($language);

            $messages = [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
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

            // Try OpenAI first, fallback to Gemini
            if ($this->openaiApiKey) {
                $response = $this->callOpenAI($messages);
                if ($response) return $response;
            }
            
            if ($this->geminiApiKey) {
                $response = $this->callGemini($message, $language);
                if ($response) return $response;
            }

            throw new \Exception('OpenAI API request failed');

        } catch (\Exception $e) {
            Log::error('AI Chat Error: ' . $e->getMessage());
            
            $language = $this->detectLanguage($message);
            if ($language === 'ar') {
                return 'عذراً، أواجه صعوبة تقنية حالياً. يرجى المحاولة مرة أخرى لاحقاً.';
            }
            return 'Sorry, I\'m experiencing technical difficulties. Please try again later.';
        }
    }

    protected function callOpenAI(array $messages): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4',
                'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }

            Log::error('OpenAI API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('OpenAI Request Error: ' . $e->getMessage());
            return null;
        }
    }

    protected function callGemini(string $message, string $language): ?string
    {
        try {
            $systemPrompt = $this->getSystemPrompt($language);
            $fullPrompt = $systemPrompt . "\n\nUser: " . $message . "\n\nAssistant:";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(30)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $this->geminiApiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 1000,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            }

            Log::error('Gemini API Error: ' . $response->body());
            return null;

        } catch (\Exception $e) {
            Log::error('Gemini Request Error: ' . $e->getMessage());
            return null;
        }
    }

    public function analyzeImage(string $imagePath, string $question = null)
    {
        try {
            if (!$this->openaiApiKey) {
                throw new \Exception('OpenAI API key not configured for image analysis');
            }

            $imageData = base64_encode(Storage::get($imagePath));
            $language = $question ? $this->detectLanguage($question) : 'en';
            $systemPrompt = $this->getSystemPrompt($language);
            
            $messages = [
                [
                    'role' => 'system',
                    'content' => $systemPrompt . ' You specialize in medical image analysis.'
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $question ?: ($language === 'ar' ? 'يرجى تحليل هذه الصورة الطبية وتقديم الملاحظات.' : 'Please analyze this medical image and provide insights.')
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
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
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
            
            $language = $question ? $this->detectLanguage($question) : 'en';
            if ($language === 'ar') {
                return 'عذراً، لا يمكنني تحليل هذه الصورة حالياً. يرجى المحاولة مرة أخرى لاحقاً.';
            }
            return 'Sorry, I cannot analyze this image at the moment. Please try again later.';
        }
    }

    public function generateImage(string $prompt)
    {
        try {
            if (!$this->openaiApiKey) {
                throw new \Exception('OpenAI API key not configured for image generation');
            }

            // Enhance prompt for medical context
            $enhancedPrompt = "Medical illustration: " . $prompt . " - professional, educational, anatomically accurate";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(120)->post('https://api.openai.com/v1/images/generations', [
                'model' => 'dall-e-3',
                'prompt' => $enhancedPrompt,
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
        try {
            // Video generation is not yet available in OpenAI API
            // This is a placeholder for future implementation
            Log::info('Video generation requested (not yet implemented): ' . $prompt);
            
            // Could integrate with services like:
            // - Runway ML
            // - Stable Video Diffusion
            // - Pika Labs
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Video Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateFlashcards(string $topic, int $count = 5)
    {
        try {
            $language = $this->detectLanguage($topic);
            
            if ($language === 'ar') {
                $prompt = "أنشئ {$count} بطاقات تعليمية طبية حول '{$topic}'. أرجع النتيجة كمصفوفة JSON مع حقول 'question' و 'answer'. ركز على المفاهيم الطبية الرئيسية والأعراض والعلاجات أو معايير التشخيص. استخدم اللغة العربية.";
            } else {
                $prompt = "Create {$count} medical flashcards about '{$topic}'. Return as JSON array with 'question' and 'answer' fields. Focus on key medical concepts, symptoms, treatments, or diagnostic criteria. Use English language.";
            }

            $messages = [
                [
                    'role' => 'system',
                    'content' => $this->getSystemPrompt($language) . ' You are specialized in creating educational medical flashcards.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ];

            // Try OpenAI first
            if ($this->openaiApiKey) {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4',
                    'messages' => $messages,
                    'max_tokens' => 1500,
                    'temperature' => 0.7,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'];
                    
                    // Try to parse JSON from the response
                    $flashcards = $this->parseFlashcardsFromResponse($content, $topic, $language);
                    
                    if (!empty($flashcards)) {
                        return $flashcards;
                    }
                }
            }

            // Fallback: create basic flashcards
            return $this->createFallbackFlashcards($topic, $count, $language);

        } catch (\Exception $e) {
            Log::error('Flashcards Generation Error: ' . $e->getMessage());
            return $this->createFallbackFlashcards($topic, $count, $this->detectLanguage($topic));
        }
    }

    protected function parseFlashcardsFromResponse(string $content, string $topic, string $language): array
    {
        // Try to extract JSON from the response
        if (preg_match('/\[.*\]/s', $content, $matches)) {
            $jsonString = $matches[0];
            $flashcards = json_decode($jsonString, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($flashcards)) {
                return $flashcards;
            }
        }

        // If JSON parsing fails, try to parse structured text
        $lines = explode("\n", $content);
        $flashcards = [];
        $currentCard = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match('/^(\d+\.|\*|-)\s*(.+)/', $line, $matches)) {
                if (!empty($currentCard)) {
                    $flashcards[] = $currentCard;
                    $currentCard = [];
                }
                $currentCard['question'] = $matches[2];
            } elseif (!empty($currentCard) && empty($currentCard['answer'])) {
                $currentCard['answer'] = $line;
            }
        }

        if (!empty($currentCard)) {
            $flashcards[] = $currentCard;
        }

        return $flashcards;
    }

    protected function createFallbackFlashcards(string $topic, int $count, string $language): array
    {
        $flashcards = [];
        
        if ($language === 'ar') {
            for ($i = 1; $i <= $count; $i++) {
                $flashcards[] = [
                    'question' => "ما هو {$topic}؟ (السؤال {$i})",
                    'answer' => "هذا سؤال تعليمي حول {$topic}. يرجى استشارة مختص طبي للحصول على معلومات دقيقة."
                ];
            }
        } else {
            for ($i = 1; $i <= $count; $i++) {
                $flashcards[] = [
                    'question' => "What is {$topic}? (Question {$i})",
                    'answer' => "This is an educational question about {$topic}. Please consult a medical professional for accurate information."
                ];
            }
        }

        return $flashcards;
    }

    public function testConnection(): array
    {
        $results = [
            'openai' => false,
            'gemini' => false,
            'message' => ''
        ];

        // Test OpenAI
        if ($this->openaiApiKey) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                ])->timeout(10)->get('https://api.openai.com/v1/models');

                $results['openai'] = $response->successful();
            } catch (\Exception $e) {
                Log::error('OpenAI connection test failed: ' . $e->getMessage());
            }
        }

        // Test Gemini
        if ($this->geminiApiKey) {
            try {
                $response = Http::timeout(10)->get("https://generativelanguage.googleapis.com/v1beta/models?key=" . $this->geminiApiKey);
                $results['gemini'] = $response->successful();
            } catch (\Exception $e) {
                Log::error('Gemini connection test failed: ' . $e->getMessage());
            }
        }

        if ($results['openai'] || $results['gemini']) {
            $results['message'] = 'AI services are connected and working.';
        } else {
            $results['message'] = 'No AI services are properly configured.';
        }

        return $results;
    }
}