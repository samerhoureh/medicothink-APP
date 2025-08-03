<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'age',
        'nationality',
        'region',
        'specialization',
        'education_level',
        'profile_image',
        'is_active',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('ends_at', '>', now());
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Helper methods
    public function hasActiveSubscription()
    {
        return $this->activeSubscription()->exists();
    }

    public function canUseTokens($amount = 1)
    {
        $subscription = $this->activeSubscription;
        if (!$subscription) return false;
        
        $plan = $subscription->plan;
        if ($plan->tokens_limit === -1) return true;
        
        return ($subscription->tokens_used + $amount) <= $plan->tokens_limit;
    }

    public function canUseImages($amount = 1)
    {
        $subscription = $this->activeSubscription;
        if (!$subscription) return false;
        
        $plan = $subscription->plan;
        if ($plan->images_limit === -1) return true;
        
        return ($subscription->images_used + $amount) <= $plan->images_limit;
    }

    public function canUseVideos($amount = 1)
    {
        $subscription = $this->activeSubscription;
        if (!$subscription) return false;
        
        $plan = $subscription->plan;
        if ($plan->videos_limit === -1) return true;
        
        return ($subscription->videos_used + $amount) <= $plan->videos_limit;
    }

    public function incrementUsage($type, $amount = 1)
    {
        $subscription = $this->activeSubscription;
        if (!$subscription) return false;

        $field = $type . '_used';
        $subscription->increment($field, $amount);
        
        return true;
    }
}