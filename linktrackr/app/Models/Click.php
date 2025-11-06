<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    use HasFactory;

    // Disable default timestamps (we use clicked_at instead)
    public $timestamps = false;

    protected $fillable = [
        'link_id',
        'ip_address',
        'country',
        'country_name',
        'city',
        'region',
        'device_type',
        'browser',
        'browser_version',
        'platform',
        'referrer',
        'referrer_domain',
        'is_qr_code',
        'user_agent',
        'clicked_at',
    ];

    protected $casts = [
        'is_qr_code' => 'boolean',
        'clicked_at' => 'datetime',
    ];

    /**
     * Get the link that owns this click
     */
    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }

    /**
     * Get formatted device type with icon
     */
    public function getDeviceIconAttribute(): string
    {
        switch ($this->device_type) {
            case 'mobile':
            case 'tablet':
                return '📱';
            case 'desktop':
                return '💻';
            default:
                return '🖥️';
        }
    }

    /**
     * Get referrer source name (simplified)
     */
    public function getReferrerSourceAttribute(): string
    {
        if (!$this->referrer) {
            return 'Direct';
        }

        $domain = $this->referrer_domain ?? parse_url($this->referrer, PHP_URL_HOST);
        
        // Simplify common sources
        if (str_contains($domain, 'google')) return 'Google';
        if (str_contains($domain, 'facebook')) return 'Facebook';
        if (str_contains($domain, 'instagram')) return 'Instagram';
        if (str_contains($domain, 'twitter') || str_contains($domain, 'x.com')) return 'Twitter/X';
        if (str_contains($domain, 'linkedin')) return 'LinkedIn';
        if (str_contains($domain, 'whatsapp')) return 'WhatsApp';
        if (str_contains($domain, 'telegram')) return 'Telegram';
        
        return $domain ?? 'Unknown';
    }

    /**
     * Scope: Get clicks within date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('clicked_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Get clicks from QR codes only
     */
    public function scopeFromQr($query)
    {
        return $query->where('is_qr_code', true);
    }

    /**
     * Scope: Get clicks by device type
     */
    public function scopeByDevice($query, $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    /**
     * Scope: Get clicks by country
     */
    public function scopeByCountry($query, $countryCode)
    {
        return $query->where('country', $countryCode);
    }
}
