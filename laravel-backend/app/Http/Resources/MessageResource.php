<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'is_from_user' => $this->is_from_user,
            'message_type' => $this->message_type,
            'image_path' => $this->image_path,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
        ];
    }
}