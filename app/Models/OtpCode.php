<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'code',
        'expires_at',
        'is_used',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessors
    public function getIsExpiredAttribute()
    {
        return $this->expires_at->isPast();
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('is_used', false)
                    ->where('expires_at', '>', now())
                    ->where('attempts', '<', 3);
    }

    // Methods
    public function markAsUsed()
    {
        $this->update(['is_used' => true]);
    }

    public function incrementAttempts()
    {
        $this->increment('attempts');
    }
}