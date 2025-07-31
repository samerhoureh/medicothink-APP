<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'is_archived',
        'last_message_at',
        'message_count',
        'summary',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'last_message_at' => 'datetime',
        'message_count' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function conversationSummary()
    {
        return $this->hasOne(ConversationSummary::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('last_message_at', 'desc');
    }

    // Methods
    public function archive()
    {
        $this->is_archived = true;
        $this->save();
    }

    public function unarchive()
    {
        $this->is_archived = false;
        $this->save();
    }

    public function updateLastMessage()
    {
        $this->last_message_at = now();
        $this->message_count = $this->messages()->count();
        $this->save();
    }

    public function generateTitle($firstMessage = null)
    {
        if ($firstMessage) {
            $title = strlen($firstMessage) > 30 
                ? substr($firstMessage, 0, 30) . '...' 
                : $firstMessage;
        } else {
            $title = 'New Conversation';
        }
        
        $this->title = $title;
        $this->save();
        
        return $title;
    }
}