<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EnhancedAiService
{
    public function textToSpeech($text, $voice = 'alloy')
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/audio/speech', [
                'model' => 'tts-1',
                'input' => $text,
                'voice' => $voice,
                'response_format' => 'mp3'
            ]);

            if ($response->successful()) {
                $filename = 'audio/tts_' . time() . '.mp3';
                Storage::put($filename, $response->body());
                return Storage::url($filename);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Text-to-Speech Error: ' . $e->getMessage());
            return null;
        }
    }

    public function speechToText($audioPath)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openai.key'),
            ])->attach(
                'file', file_get_contents($audioPath), basename($audioPath)
            )->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'response_format' => 'json'
            ]);

            if ($response->successful()) {
                return $response->json()['text'];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Speech-to-Text Error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateImage($prompt, $size = '1024x1024')
    {
        try {
            $response = OpenAI::images()->create([
                'model' => 'dall-e-3',
                'prompt' => $prompt,
                'size' => $size,
                'quality' => 'standard',
                'n' => 1,
            ]);

            $imageUrl = $response->data[0]->url;
            
            // Download and store the image
            $imageContent = Http::get($imageUrl)->body();
            $filename = 'images/generated_' . time() . '.png';
            Storage::put($filename, $imageContent);
            
            return Storage::url($filename);

        } catch (\Exception $e) {
            Log::error('Image Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    public function generateVideo($prompt)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.stability.key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.stability.ai/v2alpha/generation/video', [
                'prompt' => $prompt,
                'aspect_ratio' => '16:9',
                'duration' => 5
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['video_url'])) {
                    // Download and store the video
                    $videoContent = Http::get($data['video_url'])->body();
                    $filename = 'videos/generated_' . time() . '.mp4';
                    Storage::put($filename, $videoContent);
                    
                    return Storage::url($filename);
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Video Generation Error: ' . $e->getMessage());
            return null;
        }
    }

    public function enhancedImageAnalysis($imagePath, $analysisType = 'general')
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            
            $systemPrompts = [
                'general' => 'You are MedicoThink, an advanced AI medical assistant. Analyze this medical image comprehensively.',
                'xray' => 'You are a radiologist AI. Analyze this X-ray image for any abnormalities or findings.',
                'skin' => 'You are a dermatology AI. Analyze this skin image for any concerning features.',
                'eye' => 'You are an ophthalmology AI. Analyze this eye image for any abnormalities.'
            ];

            $messages = [
                [
                    'role' => 'system',
                    'content' => $systemPrompts[$analysisType] ?? $systemPrompts['general']
                ],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Please provide a detailed medical analysis of this image, including any observations, potential concerns, and recommendations for follow-up.'
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
                'max_tokens' => 1500,
                'temperature' => 0.3,
            ]);

            return $response->choices[0]->message->content;

        } catch (\Exception $e) {
            Log::error('Enhanced Image Analysis Error: ' . $e->getMessage());
            return 'Unable to analyze the image at this time. Please try again later.';
        }
    }
}