<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Link extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'short_code',
        'original_url',
        'custom_alias',
        'qr_code_png',
        'qr_code_svg',
        'title',
        'description',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the link
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all clicks for this link
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    /**
     * Get the short URL for this link
     */
    public function getShortUrlAttribute(): string
    {
        $domain = config('app.url');
        $code = $this->custom_alias ?? $this->short_code;
        return "{$domain}/{$code}";
    }

    /**
     * Get total clicks count
     */
    public function getTotalClicksAttribute(): int
    {
        return $this->clicks()->count();
    }

    /**
     * Get clicks from QR codes
     */
    public function getQrClicksAttribute(): int
    {
        return $this->clicks()->where('is_qr_code', true)->count();
    }

    /**
     * Get direct link clicks (non-QR)
     */
    public function getDirectClicksAttribute(): int
    {
        return $this->clicks()->where('is_qr_code', false)->count();
    }

    /**
     * Check if link is expired
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return now()->isAfter($this->expires_at);
    }

    /**
     * Check if link is accessible
     */
    public function isAccessible(): bool
    {
        return $this->is_active && !$this->isExpired();
    }

    /**
     * Scope: Get only active links
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get user's links ordered by recent
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get analytics summary for this link
     */
    public function getAnalyticsSummary(): array
    {
        return [
            'total_clicks' => $this->clicks()->count(),
            'qr_clicks' => $this->clicks()->where('is_qr_code', true)->count(),
            'direct_clicks' => $this->clicks()->where('is_qr_code', false)->count(),
            'unique_countries' => $this->clicks()->distinct('country')->count('country'),
            'mobile_clicks' => $this->clicks()->where('device_type', 'mobile')->count(),
            'desktop_clicks' => $this->clicks()->where('device_type', 'desktop')->count(),
            'last_clicked_at' => ($latestClick = $this->clicks()->latest('clicked_at')->first()) 
                    ? $latestClick->clicked_at 
                    : null,        ];
    }
}
