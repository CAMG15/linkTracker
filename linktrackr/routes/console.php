<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clean up expired links (run daily)
Artisan::command('links:cleanup-expired', function () {
    $deleted = \App\Models\Link::where('expires_at', '<', now())
        ->where('is_active', true)
        ->update(['is_active' => false]);
    
    $this->info("Deactivated {$deleted} expired links");
})->purpose('Deactivate expired links');

// Generate analytics report
Artisan::command('analytics:daily-report', function () {
    $yesterday = now()->subDay();
    
    $totalClicks = \App\Models\Click::whereDate('clicked_at', $yesterday)->count();
    $uniqueLinks = \App\Models\Click::whereDate('clicked_at', $yesterday)
        ->distinct('link_id')
        ->count('link_id');
    
    $this->info("Daily Analytics Report for " . $yesterday->format('Y-m-d'));
    $this->line("Total Clicks: {$totalClicks}");
    $this->line("Unique Links Clicked: {$uniqueLinks}");
})->purpose('Generate daily analytics report');
