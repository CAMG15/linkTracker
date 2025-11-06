<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
     private AnalyticsService $analyticsService;

     public function __construct(
        AnalyticsService $analyticsService //  
    ) {
         $this->analyticsService = $analyticsService; 
        
     }

    /**
     * Redirect to original URL and track click
     * 
     * This is the main entry point for shortened URLs
     * URL pattern: /{code} or /{custom_alias}
     */
    public function redirect(Request $request, string $code)
    {
        // Find link by short code or custom alias
        $link = Link::where('short_code', $code)
            ->orWhere('custom_alias', $code)
            ->first();

        // Handle not found
        if (!$link) {
            abort(404, 'Short link not found');
        }

        // Check if link is accessible
        if (!$link->isAccessible()) {
            if ($link->isExpired()) {
                abort(410, 'This link has expired');
            }
            abort(403, 'This link is no longer active');
        }

        // Check if this is a QR code click
        $isQrCode = $request->has('qr') || $request->header('X-QR-Scan');

        // Track the click asynchronously (after redirect for better performance)
        dispatch(function () use ($link, $request, $isQrCode) {
            $this->analyticsService->trackClick($link, $request, $isQrCode);
        })->afterResponse();

        // Redirect to original URL (301 = permanent, 302 = temporary)
        return redirect()->away($link->original_url, 302);
    }

    /**
     * Preview link information (optional feature)
     * URL pattern: /{code}/preview
     */
    public function preview(string $code)
    {
        $link = Link::where('short_code', $code)
            ->orWhere('custom_alias', $code)
            ->firstOrFail();

        return view('links.preview', compact('link'));
    }
}