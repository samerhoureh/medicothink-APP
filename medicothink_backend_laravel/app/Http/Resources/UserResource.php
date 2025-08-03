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
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'age' => $this->age,
            'nationality' => $this->nationality,
            'region' => $this->region,
            'specialization' => $this->specialization,
            'education_level' => $this->education_level,
            'profile_image' => $this->profile_image ? asset('storage/' . $this->profile_image) : null,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'subscription' => new SubscriptionResource($this->whenLoaded('activeSubscription')),
        ];
    }
}