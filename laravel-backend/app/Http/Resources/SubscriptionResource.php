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
            'plan_name' => $this->plan_name,
            'plan_type' => $this->plan_type,
            'price' => $this->price,
            'currency' => $this->currency,
            'starts_at' => $this->starts_at,
            'expires_at' => $this->expires_at,
            'is_active' => $this->is_active && !$this->is_expired,
            'is_expired' => $this->is_expired,
            'is_expiring_soon' => $this->is_expiring_soon,
            'days_remaining' => $this->days_remaining,
            'status_text' => $this->status_text,
            'auto_renew' => $this->auto_renew,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}