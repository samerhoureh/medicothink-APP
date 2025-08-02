<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'title',
        'summary',
        'key_points',
        'recommendations',
        'diagnosis',
        'symptoms',
        'treatment',
    ];

    protected $casts = [
        'key_points' => 'array',
        'recommendations' => 'array',
        'symptoms' => 'array',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}