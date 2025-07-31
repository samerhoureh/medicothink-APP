<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'age' => $this->age,
            'city' => $this->city,
            'nationality' => $this->nationality,
            'specialization' => $this->specialization,
            'education_level' => $this->education_level,
            'profile_image' => $this->profile_image,
            'is_active' => $this->is_active,
            'is_verified' => !is_null($this->email_verified_at),
            'phone_verified' => !is_null($this->phone_verified_at),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'subscription' => $this->when($this->subscription, function () {
                return [
                    'id' => $this->subscription->id,
                    'plan_name' => $this->subscription->plan_name,
                    'plan_type' => $this->subscription->plan_type,
                    'expires_at' => $this->subscription->expires_at,
                    'started_at' => $this->subscription->starts_at,
                    'is_active' => $this->subscription->is_active && !$this->subscription->is_expired,
                    'days_remaining' => $this->subscription->days_remaining,
                    'price' => $this->subscription->price,
                    'currency' => $this->subscription->currency,
                    'auto_renew' => $this->subscription->auto_renew,
                ];
            }),
        ];
    }
}