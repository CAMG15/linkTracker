<?php

// =============================================================================
// FILE: app/Services/AnalyticsService.php
// Purpose: Track clicks and extract analytics data from requests
// =============================================================================

namespace App\Services;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

class AnalyticsService
{
    /**
     * Track a click on a short link
     * 
     * @param Link $link
     * @param Request $request
     * @param bool $isQrCode
     * @return Click
     */
    public function trackClick(Link $link, Request $request, bool $isQrCode = false): Click
    {
        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());
        
        // Get IP address (handle proxies)
        $ipAddress = $this->getClientIp($request);
        
        // Get geographic location
        $location = $this->getLocation($ipAddress);
        
        // Parse referrer
        $referrer = $request->header('referer');
        $referrerDomain = $referrer ? parse_url($referrer, PHP_URL_HOST) : null;
        
        return Click::create([
            'link_id' => $link->id,
            'ip_address' => $ipAddress,
            'country' => $location['country'] ?? null,
            'country_name' => $location['country_name'] ?? null,
            'city' => $location['city'] ?? null,
            'region' => $location['region'] ?? null,
            'device_type' => $this->getDeviceType($agent),
            'browser' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser()),
            'platform' => $agent->platform(),
            'referrer' => $referrer,
            'referrer_domain' => $referrerDomain,
            'is_qr_code' => $isQrCode,
            'user_agent' => $request->userAgent(),
            'clicked_at' => now(),
        ]);
    }

    /**
     * Get device type from user agent
     * 
     * @param Agent $agent
     * @return string 'mobile', 'tablet', or 'desktop'
     */
    private function getDeviceType(Agent $agent): string
    {
        if ($agent->isMobile()) {
            return 'mobile';
        }
        
        if ($agent->isTablet()) {
            return 'tablet';
        }
        
        return 'desktop';
    }

    /**
     * Get client IP address (handling proxies and load balancers)
     * 
     * @param Request $request
     * @return string
     */
    private function getClientIp(Request $request): string
    {
        // Check for common proxy headers
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // General proxy
            'HTTP_X_REAL_IP',        // Nginx
            'REMOTE_ADDR',           // Direct connection
        ];
        
        foreach ($headers as $header) {
            $ip = $request->server($header);
            if ($ip) {
                // If multiple IPs, take the first one
                $ips = explode(',', $ip);
                return trim($ips[0]);
            }
        }
        
        return $request->ip();
    }

    /**
     * Get geographic location from IP address
     * 
     * @param string $ipAddress
     * @return array
     */
    private function getLocation(string $ipAddress): array
    {
        try {
            // Skip location lookup for local IPs
            if ($this->isLocalIp($ipAddress)) {
                return [
                    'country' => 'XX',
                    'country_name' => 'Local',
                    'city' => 'Local',
                    'region' => 'Local',
                ];
            }
            
            // Use stevebauman/location package
            $position = Location::get($ipAddress);
            
            if ($position) {
                return [
                    'country' => $position->countryCode,
                    'country_name' => $position->countryName,
                    'city' => $position->cityName,
                    'region' => $position->regionName,
                ];
            }
        } catch (\Exception $e) {
            // Log error but don't fail the tracking
            \Log::warning("Failed to get location for IP {$ipAddress}: " . $e->getMessage());
        }
        
        return [
            'country' => null,
            'country_name' => null,
            'city' => null,
            'region' => null,
        ];
    }

    /**
     * Check if IP is local/private
     * 
     * @param string $ip
     * @return bool
     */
    private function isLocalIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1']) || 
               str_starts_with($ip, '192.168.') ||
               str_starts_with($ip, '10.') ||
               str_starts_with($ip, '172.');
    }

    /**
     * Get analytics summary for a link
     * 
     * @param Link $link
     * @return array
     */
    public function getLinkAnalytics(Link $link): array
    {
        return [
            'total_clicks' => $link->clicks()->count(),
            'clicks_by_date' => $this->getClicksByDate($link),
            'clicks_by_country' => $this->getClicksByCountry($link),
            'clicks_by_device' => $this->getClicksByDevice($link),
            'clicks_by_referrer' => $this->getClicksByReferrer($link),
            'qr_vs_direct' => [
                'qr' => $link->clicks()->where('is_qr_code', true)->count(),
                'direct' => $link->clicks()->where('is_qr_code', false)->count(),
            ],
        ];
    }

    private function getClicksByDate(Link $link): array
    {
        return $link->clicks()
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->toArray();
    }

    private function getClicksByCountry(Link $link): array
    {
        return $link->clicks()
            ->selectRaw('country_name, COUNT(*) as count')
            ->whereNotNull('country_name')
            ->groupBy('country_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getClicksByDevice(Link $link): array
    {
        return $link->clicks()
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get()
            ->toArray();
    }

    private function getClicksByReferrer(Link $link): array
    {
        return $link->clicks()
            ->selectRaw('referrer_domain, COUNT(*) as count')
            ->whereNotNull('referrer_domain')
            ->groupBy('referrer_domain')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}