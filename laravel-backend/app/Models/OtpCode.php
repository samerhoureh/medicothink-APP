<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone_number',
        'code',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'attempts' => 'integer',
    ];

    const MAX_ATTEMPTS = 3;
    const EXPIRY_MINUTES = 5;

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->whereNull('verified_at')
                    ->where('expires_at', '>', now())
                    ->where('attempts', '<', self::MAX_ATTEMPTS);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    // Methods
    public function isValid()
    {
        return is_null($this->verified_at) && 
               $this->expires_at > now() && 
               $this->attempts < self::MAX_ATTEMPTS;
    }

    public function isExpired()
    {
        return $this->expires_at <= now();
    }

    public function verify()
    {
        $this->verified_at = now();
        $this->save();
    }

    public function incrementAttempts()
    {
        $this->attempts++;
        $this->save();
    }

    public static function generateCode()
    {
        return str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public static function createForUser($userId, $phoneNumber)
    {
        // Invalidate existing codes
        self::where('user_id', $userId)
            ->where('phone_number', $phoneNumber)
            ->whereNull('verified_at')
            ->delete();

        return self::create([
            'user_id' => $userId,
            'phone_number' => $phoneNumber,
            'code' => self::generateCode(),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            'attempts' => 0,
        ]);
    }
}