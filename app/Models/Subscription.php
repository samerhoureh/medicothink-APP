<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_name',
        'plan_type',
        'price',
        'currency',
        'status',
        'starts_at',
        'ends_at',
        'stripe_subscription_id',
        'paypal_subscription_id',
        'auto_renewal',
        'features'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'auto_renewal' => 'boolean',
        'features' => 'array',
        'price' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isActive()
    {
        return $this->status === 'active' && $this->ends_at > now();
    }

    public function isExpired()
    {
        return $this->ends_at < now();
    }
}