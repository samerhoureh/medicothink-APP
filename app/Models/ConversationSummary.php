<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'summary',
        'key_points',
        'medical_insights',
        'recommendations'
    ];

    protected $casts = [
        'key_points' => 'array',
        'medical_insights' => 'array',
        'recommendations' => 'array'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}