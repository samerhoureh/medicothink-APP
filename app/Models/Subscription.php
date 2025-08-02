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
        'payment_method',
        'transaction_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'auto_renew' => 'boolean',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsExpiringSoonAttribute()
    {
        if (!$this->expires_at) return false;
        return $this->expires_at->diffInDays(now()) <= 7 && !$this->is_expired;
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->expires_at || $this->is_expired) return 0;
        return max(0, $this->expires_at->diffInDays(now()));
    }

    public function getStatusTextAttribute()
    {
        if ($this->is_expired) return 'Expired';
        if ($this->is_expiring_soon) return 'Expiring Soon';
        if ($this->is_active) return 'Active';
        return 'Inactive';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeExpiringSoon($query)
    {
        return $query->where('expires_at', '<=', now()->addDays(7))
                    ->where('expires_at', '>', now());
    }
}