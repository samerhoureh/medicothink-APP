<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $locale = $request->header('Accept-Language', 'en');
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->getDisplayName($locale),
            'price' => $this->price,
            'currency' => $this->currency,
            'duration' => $this->duration,
            'limits' => [
                'tokens' => $this->tokens_limit,
                'images' => $this->images_limit,
                'videos' => $this->videos_limit,
                'conversations' => $this->conversations_limit,
            ],
            'features' => $this->features,
            'is_popular' => $this->is_popular,
            'is_active' => $this->is_active,
        ];
    }
}