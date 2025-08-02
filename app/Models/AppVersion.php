<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_name',
        'version_code',
        'platform',
        'download_url',
        'is_required',
        'is_active',
        'release_notes',
        'min_supported_version',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'release_notes' => 'array',
    ];

    const PLATFORM_ANDROID = 'android';
    const PLATFORM_IOS = 'ios';

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }
}