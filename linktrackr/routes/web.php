<?php

use App\Http\Controllers\LinkController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| Laravel Breeze provides these routes automatically:
| - GET  /register
| - POST /register
| - GET  /login
| - POST /login
| - POST /logout
| - GET  /forgot-password
| - POST /forgot-password
| - GET  /reset-password/{token}
| - POST /reset-password
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Home page (redirects to dashboard if authenticated)
Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('dashboard')
        : view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [LinkController::class, 'index'])->name('dashboard');
    
    // Link Management
    Route::prefix('links')->name('links.')->group(function () {
        Route::get('/create', [LinkController::class, 'create'])->name('create');
        Route::post('/', [LinkController::class, 'store'])->name('store');
        Route::get('/{link}', [LinkController::class, 'show'])->name('show');
        Route::patch('/{link}', [LinkController::class, 'update'])->name('update');
        Route::delete('/{link}', [LinkController::class, 'destroy'])->name('destroy');
        
        // QR Code Downloads
        Route::get('/{link}/qr/{format}', [LinkController::class, 'downloadQr'])
            ->where('format', 'png|svg')
            ->name('qr.download');
    });
    
    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/{link}', [AnalyticsController::class, 'show'])->name('show');
        Route::get('/{link}/export-csv', [AnalyticsController::class, 'exportCsv'])->name('export');
        Route::get('/{link}/chart-data', [AnalyticsController::class, 'chartData'])->name('chart-data');
    });
    
});

/*
|--------------------------------------------------------------------------
| Short Link Redirection Routes (Public)
|--------------------------------------------------------------------------
| IMPORTANT: These must be at the bottom to avoid conflicting with other routes
*/

// Preview link (optional feature)
Route::get('/{code}/preview', [RedirectController::class, 'preview'])->name('link.preview');

// Main redirection route - catches all short codes
Route::get('/{code}', [RedirectController::class, 'redirect'])
    ->where('code', '[a-zA-Z0-9-]+')
    ->name('link.redirect');