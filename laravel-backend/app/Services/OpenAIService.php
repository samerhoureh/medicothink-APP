<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Collection;

class OpenAIService
{
    public function generateResponse(string $message, Collection $conversationHistory = null)
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a professional medical AI assistant. Provide helpful, accurate medical information while always recommending users consult with healthcare professionals for proper diagnosis and treatment. Be empathetic and professional in your responses.'
            ]
        ];

        // Add conversation history
        if ($conversationHistory && $conversationHistory->count() > 0) {
            foreach ($conversationHistory->take(-10) as $msg) { // Last 10 messages for context
                $messages[] = [
                    'role' => $msg->is_from_user ? 'user' : 'assistant',
                    'content' => $msg->content
                ];
            }
        }

        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7,
        ]);

        return $response->choices[0]->message->content;
    }

    public function analyzeImage(string $imagePath)
    {
        // For image analysis, we'll use a simulated response
        // In production, you would use GPT-4 Vision or specialized medical AI
        
        $responses = [
            "I can see what appears to be a skin condition in the image. Based on the visual characteristics, this could be eczema or dermatitis. I recommend consulting with a dermatologist for proper diagnosis and treatment.",
            "The image shows what looks like a rash or skin irritation. It's important to keep the area clean and avoid scratching. Please consult a healthcare provider for proper evaluation.",
            "I can observe some symptoms in the image. While I can provide general information, it's crucial to have this examined by a medical professional for accurate diagnosis and appropriate treatment.",
            "The image appears to show a medical concern that would benefit from professional evaluation. I recommend scheduling an appointment with your healthcare provider to discuss this properly.",
        ];

        // Simulate processing time
        sleep(2);

        return $responses[array_rand($responses)];
    }

    public function generateConversationSummary(Collection $messages)
    {
        $conversationText = $messages->map(function ($message) {
            $role = $message->is_from_user ? 'Patient' : 'AI Assistant';
            return "{$role}: {$message->content}";
        })->join("\n");

        $prompt = "Please analyze the following medical conversation and provide a structured summary:\n\n{$conversationText}\n\nProvide the response in the following JSON format:\n{\n  \"summary\": \"Brief overview of the conversation\",\n  \"key_points\": [\"point1\", \"point2\", \"point3\"],\n  \"recommendations\": [\"recommendation1\", \"recommendation2\"],\n  \"diagnosis\": \"Potential diagnosis or null if none discussed\",\n  \"symptoms\": [\"symptom1\", \"symptom2\"],\n  \"treatment\": \"Treatment plan or null if none discussed\"\n}";

        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a medical AI that creates structured summaries of patient conversations. Always provide responses in valid JSON format.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'max_tokens' => 800,
            'temperature' => 0.3,
        ]);

        $jsonResponse = $response->choices[0]->message->content;
        
        try {
            return json_decode($jsonResponse, true);
        } catch (\Exception $e) {
            // Fallback if JSON parsing fails
            return [
                'summary' => 'Medical conversation summary',
                'key_points' => ['Patient discussed health concerns', 'AI provided medical guidance'],
                'recommendations' => ['Consult with healthcare provider'],
                'diagnosis' => null,
                'symptoms' => ['General health inquiry'],
                'treatment' => null
            ];
        }
    }
}