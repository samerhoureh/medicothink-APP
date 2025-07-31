<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_name',
        'plan_type',
        'price',
        'currency',
        'starts_at',
        'expires_at',
        'is_active',
        'auto_renew',
        'stripe_subscription_id',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'auto_renew' => 'boolean',
        'metadata' => 'array',
    ];

    const PLAN_BASIC = 'basic';
    const PLAN_PREMIUM = 'premium';
    const PLAN_PROFESSIONAL = 'professional';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getDaysRemainingAttribute()
    {
        if ($this->expires_at <= now()) {
            return 0;
        }
        
        return now()->diffInDays($this->expires_at);
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at <= now();
    }

    public function getIsExpiringSoonAttribute()
    {
        return $this->days_remaining <= 7 && $this->days_remaining > 0;
    }

    public function getStatusTextAttribute()
    {
        if ($this->is_expired) {
            return 'Expired';
        }
        
        if ($this->is_expiring_soon) {
            return 'Expiring Soon';
        }
        
        if ($this->is_active) {
            return 'Active';
        }
        
        return 'Inactive';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('expires_at', '<=', now()->addDays($days))
                    ->where('expires_at', '>', now());
    }

    // Methods
    public function activate()
    {
        $this->is_active = true;
        $this->save();
    }

    public function deactivate()
    {
        $this->is_active = false;
        $this->save();
    }

    public function extend($days)
    {
        $this->expires_at = $this->expires_at->addDays($days);
        $this->save();
    }

    public function renew($duration = 30)
    {
        $this->starts_at = now();
        $this->expires_at = now()->addDays($duration);
        $this->is_active = true;
        $this->save();
    }

    public function cancel()
    {
        $this->is_active = false;
        $this->auto_renew = false;
        $this->save();
    }
}