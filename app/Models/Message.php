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
        'is_user',
        'message_type',
        'image_path',
        'metadata',
    ];

    protected $casts = [
        'is_user' => 'boolean',
        'metadata' => 'array',
    ];

    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // Scopes
    public function scopeUserMessages($query)
    {
        return $query->where('is_user', true);
    }

    public function scopeAiMessages($query)
    {
        return $query->where('is_user', false);
    }

    public function scopeWithImages($query)
    {
        return $query->where('message_type', self::TYPE_IMAGE);
    }
}