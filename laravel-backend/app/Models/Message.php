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
        'metadata',
    ];

    protected $casts = [
        'is_from_user' => 'boolean',
        'metadata' => 'array',
    ];

    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    const TYPE_SYSTEM = 'system';

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

    public function scopeFromAI($query)
    {
        return $query->where('is_from_user', false);
    }

    public function scopeTextMessages($query)
    {
        return $query->where('message_type', self::TYPE_TEXT);
    }

    public function scopeImageMessages($query)
    {
        return $query->where('message_type', self::TYPE_IMAGE);
    }

    // Methods
    public function isFromUser()
    {
        return $this->is_from_user;
    }

    public function isFromAI()
    {
        return !$this->is_from_user;
    }

    public function hasImage()
    {
        return $this->message_type === self::TYPE_IMAGE && !empty($this->image_path);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($message) {
            $message->conversation->updateLastMessage();
        });
    }
}