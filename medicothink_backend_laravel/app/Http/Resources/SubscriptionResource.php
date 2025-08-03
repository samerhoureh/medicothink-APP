<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'auto_renewal' => $this->auto_renewal,
            'usage' => [
                'tokens_used' => $this->tokens_used,
                'images_used' => $this->images_used,
                'videos_used' => $this->videos_used,
                'conversations_used' => $this->conversations_used,
            ],
            'remaining' => [
                'tokens' => $this->getRemainingTokens(),
                'images' => $this->getRemainingImages(),
                'videos' => $this->getRemainingVideos(),
            ],
            'days_until_expiry' => $this->daysUntilExpiry(),
            'is_active' => $this->isActive(),
            'plan' => new SubscriptionPlanResource($this->whenLoaded('plan')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}