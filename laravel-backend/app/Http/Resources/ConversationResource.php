<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'is_archived' => $this->is_archived,
            'message_count' => $this->message_count,
            'last_message_at' => $this->last_message_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'latest_message' => $this->when($this->latestMessage, function () {
                return [
                    'id' => $this->latestMessage->id,
                    'content' => $this->latestMessage->content,
                    'is_from_user' => $this->latestMessage->is_from_user,
                    'message_type' => $this->latestMessage->message_type,
                    'created_at' => $this->latestMessage->created_at,
                ];
            }),
        ];
    }
}