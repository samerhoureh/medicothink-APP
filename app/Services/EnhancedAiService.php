<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class EnhancedAiService
{
    protected $openaiKey;
    protected $elevenLabsKey;
    protected $stabilityKey;

    public function __construct()
    {
        $this->openaiKey = config('services.openai.api_key');
        $this->elevenLabsKey = config('services.elevenlabs.api_key');
        $this->stabilityKey = config('services.stability.api_key');
    }

    // Text Generation (GPT-4)
    public function generateTextResponse($message, Conversation $conversation)
    {
        $context = $this->buildContext($conversation);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a medical AI assistant. Provide helpful medical information while always recommending users consult with healthcare professionals for proper diagnosis and treatment.'
                ],
                [
                    'role' => 'user',
                    'content' => $context . "\n\nUser: " . $message
                ]
            ],
            'max_tokens' => 1000,
            'temperature' => 0.7,
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        }

        throw new \Exception('Failed to generate AI response');
    }

    // Image Analysis (GPT-4 Vision)
    public function analyzeImage($imagePath, $prompt = null)
    {
        $imageUrl = Storage::url($imagePath);
        $fullImageUrl = config('app.url') . $imageUrl;

        $messages = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $prompt ?: 'Analyze this medical image and provide insights. What do you observe? Please note that this is for educational purposes and should not replace professional medical diagnosis.'
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $fullImageUrl
                        ]
                    ]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4-vision-preview',
            'messages' => $messages,
            'max_tokens' => 1000,
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        }

        throw new \Exception('Failed to analyze image');
    }

    // Text to Speech (ElevenLabs)
    public function textToSpeech($text, $voiceId = 'default')
    {
        $response = Http::withHeaders([
            'xi-api-key' => $this->elevenLabsKey,
            'Content-Type' => 'application/json',
        ])->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
            'text' => $text,
            'model_id' => 'eleven_monolingual_v1',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.5,
            ]
        ]);

        if ($response->successful()) {
            $audioContent = $response->body();
            $filename = 'audio/' . uniqid() . '.mp3';
            Storage::put($filename, $audioContent);
            return $filename;
        }

        throw new \Exception('Failed to generate speech');
    }

    // Speech to Text (OpenAI Whisper)
    public function speechToText($audioPath)
    {
        $audioFile = Storage::path($audioPath);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiKey,
        ])->attach('file', file_get_contents($audioFile), basename($audioFile))
          ->post('https://api.openai.com/v1/audio/transcriptions', [
              'model' => 'whisper-1',
          ]);

        if ($response->successful()) {
            return $response->json()['text'];
        }

        throw new \Exception('Failed to transcribe audio');
    }

    // Image Generation (DALL-E 3)
    public function generateImage($prompt, $size = '1024x1024')
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/images/generations', [
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => $size,
            'quality' => 'standard',
        ]);

        if ($response->successful()) {
            $imageUrl = $response->json()['data'][0]['url'];
            
            // Download and store the image
            $imageContent = Http::get($imageUrl)->body();
            $filename = 'generated_images/' . uniqid() . '.png';
            Storage::put($filename, $imageContent);
            
            return $filename;
        }

        throw new \Exception('Failed to generate image');
    }

    // Video Generation (Stability AI)
    public function generateVideo($prompt, $duration = 5)
    {
        // This is a placeholder for video generation
        // You would integrate with services like RunwayML, Stability AI, or similar
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->stabilityKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.stability.ai/v1/generation/stable-video-diffusion/text-to-video', [
            'prompt' => $prompt,
            'duration' => $duration,
            'aspect_ratio' => '16:9',
        ]);

        if ($response->successful()) {
            $videoUrl = $response->json()['video_url'];
            
            // Download and store the video
            $videoContent = Http::get($videoUrl)->body();
            $filename = 'generated_videos/' . uniqid() . '.mp4';
            Storage::put($filename, $videoContent);
            
            return $filename;
        }

        throw new \Exception('Failed to generate video');
    }

    // Enhanced Summary with AI
    public function generateEnhancedSummary(Conversation $conversation)
    {
        $messages = $conversation->messages()->orderBy('created_at')->get();
        $conversationText = $messages->pluck('content')->implode("\n");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a medical AI that creates detailed, structured summaries of medical conversations. Extract key information, symptoms, recommendations, and organize them clearly.'
                ],
                [
                    'role' => 'user',
                    'content' => "Please analyze this medical conversation and provide a comprehensive summary with the following structure:
                    
                    1. Main Health Concern
                    2. Symptoms Discussed
                    3. Key Recommendations
                    4. Potential Diagnosis (if discussed)
                    5. Suggested Treatment/Actions
                    6. Important Notes
                    
                    Conversation:\n" . $conversationText
                ]
            ],
            'max_tokens' => 2000,
            'temperature' => 0.3,
        ]);

        if ($response->successful()) {
            $summary = $response->json()['choices'][0]['message']['content'];
            
            return [
                'title' => $conversation->title,
                'summary' => $summary,
                'key_points' => $this->extractKeyPoints($summary),
                'recommendations' => $this->extractRecommendations($summary),
                'diagnosis' => $this->extractDiagnosis($summary),
                'symptoms' => $this->extractSymptoms($summary),
                'treatment' => $this->extractTreatment($summary),
            ];
        }

        throw new \Exception('Failed to generate summary');
    }

    private function buildContext(Conversation $conversation)
    {
        $recentMessages = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse();

        $context = "Previous conversation context:\n";
        foreach ($recentMessages as $message) {
            $role = $message->is_user ? 'User' : 'Assistant';
            $context .= "$role: {$message->content}\n";
        }

        return $context;
    }

    private function extractKeyPoints($text)
    {
        $lines = explode("\n", $text);
        $keyPoints = [];
        
        foreach ($lines as $line) {
            if (strpos($line, '•') !== false || strpos($line, '-') !== false || strpos($line, '*') !== false) {
                $keyPoints[] = trim(str_replace(['•', '-', '*'], '', $line));
            }
        }

        return array_filter($keyPoints);
    }

    private function extractRecommendations($text)
    {
        $recommendations = [];
        if (preg_match_all('/recommend[s]?\s+(.+?)[\.\n]/i', $text, $matches)) {
            $recommendations = $matches[1];
        }
        return $recommendations;
    }

    private function extractDiagnosis($text)
    {
        if (preg_match('/diagnos[is|e]\s*:?\s*(.+?)[\.\n]/i', $text, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function extractSymptoms($text)
    {
        $symptoms = [];
        if (preg_match_all('/symptom[s]?\s*:?\s*(.+?)[\.\n]/i', $text, $matches)) {
            $symptoms = explode(',', $matches[1][0]);
            $symptoms = array_map('trim', $symptoms);
        }
        return $symptoms;
    }

    private function extractTreatment($text)
    {
        if (preg_match('/treatment\s*:?\s*(.+?)[\.\n]/i', $text, $matches)) {
            return $matches[1];
        }
        return null;
    }
}