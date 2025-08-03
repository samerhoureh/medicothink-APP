<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name_en',
        'display_name_ar',
        'price',
        'currency',
        'duration',
        'tokens_limit',
        'images_limit',
        'videos_limit',
        'conversations_limit',
        'features',
        'is_active',
        'is_popular',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'tokens_limit' => 'integer',
        'images_limit' => 'integer',
        'videos_limit' => 'integer',
        'conversations_limit' => 'integer',
    ];

    // Relationships
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    // Helper methods
    public function getDisplayName($locale = 'en')
    {
        return $locale === 'ar' ? $this->display_name_ar : $this->display_name_en;
    }

    public function hasUnlimitedTokens()
    {
        return $this->tokens_limit === -1;
    }

    public function hasUnlimitedImages()
    {
        return $this->images_limit === -1;
    }

    public function hasUnlimitedVideos()
    {
        return $this->videos_limit === -1;
    }

    public function hasUnlimitedConversations()
    {
        return $this->conversations_limit === -1;
    }
}