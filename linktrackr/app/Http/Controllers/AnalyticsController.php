<?php
namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AnalyticsController extends Controller
{
    private AnalyticsService $analyticsService;

    
    public function __construct(
        AnalyticsService $analyticsService //  
    ) {
        $this->analyticsService = $analyticsService; 
        
        $this->middleware('auth');
    }

    /**
     * Display analytics for a specific link
     */
    public function show(Link $link)
    {
        $this->authorize('view', $link);

        $analytics = $this->analyticsService->getLinkAnalytics($link);

        return view('analytics.show', compact('link', 'analytics'));
    }

    /**
     * Export analytics data as CSV (Bonus feature)
     */
    public function exportCsv(Link $link)
    {
        $this->authorize('view', $link);

        $clicks = $link->clicks()
            ->orderBy('clicked_at', 'desc')
            ->get();

        $csvData = $this->generateCsvData($clicks);

        $filename = 'analytics-' . ($link->custom_alias ?? $link->short_code) . '-' . date('Y-m-d') . '.csv';

        return Response::make($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Generate CSV data from clicks
     */
    private function generateCsvData($clicks): string
    {
        $csv = "Date,Time,Country,City,Device,Browser,Referrer,QR Code\n";

        foreach ($clicks as $click) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                $click->clicked_at->format('Y-m-d'),
                $click->clicked_at->format('H:i:s'),
                $click->country_name ?? 'Unknown',
                $click->city ?? 'Unknown',
                $click->device_type,
                $click->browser ?? 'Unknown',
                $click->referrer_source,
                $click->is_qr_code ? 'Yes' : 'No'
            );
        }

        return $csv;
    }

    /**
     * Get analytics data for charts (AJAX endpoint)
     */
    public function chartData(Link $link, Request $request)
    {
        $this->authorize('view', $link);

        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        // Clicks over time
        $clicksOverTime = $link->clicks()
            ->selectRaw('DATE(clicked_at) as date, COUNT(*) as count')
            ->where('clicked_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Device breakdown
        $deviceBreakdown = $link->clicks()
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->get();

        // Top countries
        $topCountries = $link->clicks()
            ->selectRaw('country_name, COUNT(*) as count')
            ->whereNotNull('country_name')
            ->groupBy('country_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        // Top referrers
        $topReferrers = $link->clicks()
            ->selectRaw('referrer_domain, COUNT(*) as count')
            ->whereNotNull('referrer_domain')
            ->groupBy('referrer_domain')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'clicks_over_time' => $clicksOverTime,
            'device_breakdown' => $deviceBreakdown,
            'top_countries' => $topCountries,
            'top_referrers' => $topReferrers,
        ]);
    }
}
