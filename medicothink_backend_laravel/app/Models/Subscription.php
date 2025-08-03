<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'auto_renewal',
        'tokens_used',
        'images_used',
        'videos_used',
        'conversations_used',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_renewal' => 'boolean',
        'tokens_used' => 'integer',
        'images_used' => 'integer',
        'videos_used' => 'integer',
        'conversations_used' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('ends_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now());
    }

    // Helper methods
    public function isActive()
    {
        return $this->status === 'active' && $this->ends_at > now();
    }

    public function isExpired()
    {
        return $this->ends_at < now();
    }

    public function daysUntilExpiry()
    {
        return $this->ends_at->diffInDays(now());
    }

    public function getRemainingTokens()
    {
        if ($this->plan->hasUnlimitedTokens()) {
            return -1; // Unlimited
        }
        
        return max(0, $this->plan->tokens_limit - $this->tokens_used);
    }

    public function getRemainingImages()
    {
        if ($this->plan->hasUnlimitedImages()) {
            return -1; // Unlimited
        }
        
        return max(0, $this->plan->images_limit - $this->images_used);
    }

    public function getRemainingVideos()
    {
        if ($this->plan->hasUnlimitedVideos()) {
            return -1; // Unlimited
        }
        
        return max(0, $this->plan->videos_limit - $this->videos_used);
    }

    public function resetUsage()
    {
        $this->update([
            'tokens_used' => 0,
            'images_used' => 0,
            'videos_used' => 0,
            'conversations_used' => 0,
        ]);
    }
}