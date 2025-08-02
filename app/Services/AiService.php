<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    public function generateResponse($message, $conversationHistory = [])
    {
        try {
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are MedicoThink, an advanced AI medical assistant. Provide helpful, accurate medical information while always recommending users consult healthcare professionals for serious concerns.'
                ]
            ];

            // Add conversation history
            foreach ($conversationHistory as $msg) {
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

            $response = OpenAI::chat()->create([
                'model' => config('services.openai.model', 'gpt-4'),
                'messages' => $messages,
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ]);

            return $response->choices[0]->message->content;

        } catch (\Exception $e) {
            Log::error('AI Service Error: ' . $e->getMessage());
            return 'I apologize, but I\'m experiencing technical difficulties. Please try again later.';
        }
    }

    public function analyzeImage($imagePath, $question = null)
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            
            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are MedicoThink, an AI medical assistant specialized in medical image analysis. Analyze medical images and provide insights while always recommending professional medical consultation.'
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

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4-vision-preview',
                'messages' => $messages,
                'max_tokens' => 1000,
            ]);

            return $response->choices[0]->message->content;

        } catch (\Exception $e) {
            Log::error('Image Analysis Error: ' . $e->getMessage());
            return 'I apologize, but I cannot analyze this image at the moment. Please try again later.';
        }
    }

    public function generateConversationSummary($messages)
    {
        try {
            $conversationText = '';
            foreach ($messages as $message) {
                $role = $message['is_from_user'] ? 'User' : 'Assistant';
                $conversationText .= "{$role}: {$message['content']}\n";
            }

            $response = OpenAI::chat()->create([
                'model' => config('services.openai.model', 'gpt-4'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a medical conversation summarizer. Create a comprehensive summary with key points, medical insights, and recommendations from the conversation.'
                    ],
                    [
                        'role' => 'user',
                        'content' => "Please summarize this medical conversation and extract key points, medical insights, and recommendations:\n\n{$conversationText}"
                    ]
                ],
                'max_tokens' => 800,
                'temperature' => 0.3,
            ]);

            $summaryText = $response->choices[0]->message->content;
            
            // Parse the summary into structured data
            return $this->parseSummary($summaryText);

        } catch (\Exception $e) {
            Log::error('Summary Generation Error: ' . $e->getMessage());
            return [
                'summary' => 'Unable to generate summary at this time.',
                'key_points' => [],
                'medical_insights' => [],
                'recommendations' => []
            ];
        }
    }

    private function parseSummary($summaryText)
    {
        // Simple parsing logic - can be enhanced
        $lines = explode("\n", $summaryText);
        $summary = '';
        $keyPoints = [];
        $medicalInsights = [];
        $recommendations = [];
        
        $currentSection = 'summary';
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            if (stripos($line, 'key points') !== false || stripos($line, 'main points') !== false) {
                $currentSection = 'key_points';
                continue;
            } elseif (stripos($line, 'medical insights') !== false || stripos($line, 'insights') !== false) {
                $currentSection = 'medical_insights';
                continue;
            } elseif (stripos($line, 'recommendations') !== false || stripos($line, 'advice') !== false) {
                $currentSection = 'recommendations';
                continue;
            }
            
            switch ($currentSection) {
                case 'key_points':
                    if (preg_match('/^[-•*]\s*(.+)/', $line, $matches)) {
                        $keyPoints[] = $matches[1];
                    }
                    break;
                case 'medical_insights':
                    if (preg_match('/^[-•*]\s*(.+)/', $line, $matches)) {
                        $medicalInsights[] = $matches[1];
                    }
                    break;
                case 'recommendations':
                    if (preg_match('/^[-•*]\s*(.+)/', $line, $matches)) {
                        $recommendations[] = $matches[1];
                    }
                    break;
                default:
                    $summary .= $line . ' ';
                    break;
            }
        }
        
        return [
            'summary' => trim($summary) ?: $summaryText,
            'key_points' => $keyPoints,
            'medical_insights' => $medicalInsights,
            'recommendations' => $recommendations
        ];
    }
}