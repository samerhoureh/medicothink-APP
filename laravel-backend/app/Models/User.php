<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'username',
        'email',
        'phone_number',
        'age',
        'city',
        'nationality',
        'specialization',
        'education_level',
        'profile_image',
        'password',
        'email_verified_at',
        'phone_verified_at',
        'is_active',
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
        'is_active' => 'boolean',
        'age' => 'integer',
    ];

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relationships
    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function otpCodes()
    {
        return $this->hasMany(OtpCode::class);
    }

    // Accessors
    public function getDisplayNameAttribute()
    {
        return $this->username ?: explode('@', $this->email)[0];
    }

    public function getInitialsAttribute()
    {
        if ($this->username) {
            $words = explode(' ', $this->username);
            return strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));
        }
        return strtoupper(substr($this->email, 0, 1));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    // Methods
    public function hasActiveSubscription()
    {
        return $this->subscription && 
               $this->subscription->is_active && 
               $this->subscription->expires_at > now();
    }

    public function isSubscriptionExpiringSoon($days = 7)
    {
        if (!$this->subscription) return false;
        
        return $this->subscription->expires_at <= now()->addDays($days) &&
               $this->subscription->expires_at > now();
    }

    public function markEmailAsVerified()
    {
        $this->email_verified_at = now();
        $this->save();
    }

    public function markPhoneAsVerified()
    {
        $this->phone_verified_at = now();
        $this->save();
    }
}