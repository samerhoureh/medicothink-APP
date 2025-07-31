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
        'generated_at',
    ];

    protected $casts = [
        'key_points' => 'array',
        'recommendations' => 'array',
        'symptoms' => 'array',
        'generated_at' => 'datetime',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    // Methods
    public function generateFlashCards()
    {
        $flashCards = [];

        // Summary card
        if ($this->summary) {
            $flashCards[] = [
                'id' => $this->id . '_summary',
                'title' => 'Conversation Summary',
                'content' => $this->summary,
                'type' => 'summary',
            ];
        }

        // Symptoms cards
        if ($this->symptoms) {
            foreach ($this->symptoms as $index => $symptom) {
                $flashCards[] = [
                    'id' => $this->id . '_symptom_' . $index,
                    'title' => 'Symptom ' . ($index + 1),
                    'content' => $symptom,
                    'type' => 'symptom',
                ];
            }
        }

        // Diagnosis card
        if ($this->diagnosis) {
            $flashCards[] = [
                'id' => $this->id . '_diagnosis',
                'title' => 'Potential Diagnosis',
                'content' => $this->diagnosis,
                'type' => 'diagnosis',
            ];
        }

        // Recommendations cards
        if ($this->recommendations) {
            foreach ($this->recommendations as $index => $recommendation) {
                $flashCards[] = [
                    'id' => $this->id . '_recommendation_' . $index,
                    'title' => 'Recommendation ' . ($index + 1),
                    'content' => $recommendation,
                    'type' => 'recommendation',
                ];
            }
        }

        // Treatment card
        if ($this->treatment) {
            $flashCards[] = [
                'id' => $this->id . '_treatment',
                'title' => 'Treatment Plan',
                'content' => $this->treatment,
                'type' => 'treatment',
            ];
        }

        // Key points cards
        if ($this->key_points) {
            foreach ($this->key_points as $index => $keyPoint) {
                $flashCards[] = [
                    'id' => $this->id . '_keypoint_' . $index,
                    'title' => 'Key Point ' . ($index + 1),
                    'content' => $keyPoint,
                    'type' => 'keyPoint',
                ];
            }
        }

        return $flashCards;
    }
}