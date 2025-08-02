<?php

namespace App\Services;

use App\Models\Conversation;
use Illuminate\Support\Facades\Http;

class AiService
{
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.openai.model', 'gpt-4');
    }

    public function generateResponse($message, Conversation $conversation)
    {
        $context = $this->buildContext($conversation);
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
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

    public function analyzeImage($imagePath)
    {
        // For now, return a mock analysis
        // In production, you would integrate with vision AI services
        $analyses = [
            "Based on the image, I can see what appears to be a skin condition. The visible characteristics suggest this could be eczema or dermatitis. I recommend consulting with a dermatologist for proper diagnosis and treatment.",
            "The image shows what looks like a rash or skin irritation. It's important to keep the area clean and avoid scratching. Please consult a healthcare provider for proper evaluation.",
            "I can observe some symptoms in the image. While I can provide general information, it's crucial to have this examined by a medical professional for accurate diagnosis and appropriate treatment.",
            "The image appears to show a medical concern that would benefit from professional evaluation. I recommend scheduling an appointment with your healthcare provider to discuss this properly."
        ];

        return $analyses[array_rand($analyses)];
    }

    public function generateSummary(Conversation $conversation)
    {
        $messages = $conversation->messages()->orderBy('created_at')->get();
        $conversationText = $messages->pluck('content')->implode("\n");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a medical AI that creates structured summaries of medical conversations. Extract key information and organize it into categories.'
                ],
                [
                    'role' => 'user',
                    'content' => "Please analyze this medical conversation and provide a structured summary:\n\n" . $conversationText
                ]
            ],
            'max_tokens' => 1500,
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
        // Simple extraction - in production, use more sophisticated NLP
        $lines = explode("\n", $text);
        $keyPoints = [];
        
        foreach ($lines as $line) {
            if (strpos($line, '•') !== false || strpos($line, '-') !== false) {
                $keyPoints[] = trim(str_replace(['•', '-'], '', $line));
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