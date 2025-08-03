<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'content',
        'is_from_user',
        'message_type',
        'image_path',
        'video_path',
        'flashcards',
        'metadata',
    ];

    protected $casts = [
        'is_from_user' => 'boolean',
        'flashcards' => 'array',
        'metadata' => 'array',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // Scopes
    public function scopeFromUser($query)
    {
        return $query->where('is_from_user', true);
    }

    public function scopeFromAi($query)
    {
        return $query->where('is_from_user', false);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('message_type', $type);
    }

    // Helper methods
    public function isFromUser()
    {
        return $this->is_from_user;
    }

    public function isFromAi()
    {
        return !$this->is_from_user;
    }

    public function hasImage()
    {
        return !empty($this->image_path);
    }

    public function hasVideo()
    {
        return !empty($this->video_path);
    }

    public function hasFlashcards()
    {
        return !empty($this->flashcards);
    }

    public function getImageUrl()
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function getVideoUrl()
    {
        return $this->video_path ? asset('storage/' . $this->video_path) : null;
    }
}