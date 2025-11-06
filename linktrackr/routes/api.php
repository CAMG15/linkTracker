<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (Bonus Feature)
|--------------------------------------------------------------------------
| These routes are prefixed with /api and use Sanctum authentication
*/

Route::middleware('auth:sanctum')->group(function () {
    
    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Links API
    Route::prefix('links')->group(function () {
        
        // List user's links
        Route::get('/', function (Request $request) {
            return $request->user()
                ->links()
                ->withCount('clicks')
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        });
        
        // Create new link
        Route::post('/', function (Request $request) {
            $validated = $request->validate([
                'original_url' => 'required|url|max:2048',
                'custom_alias' => 'nullable|string|min:3|max:50|unique:links,custom_alias',
                'title' => 'nullable|string|max:255',
            ]);
            
            $codeGenerator = app(\App\Services\ShortCodeGenerator::class);
            $qrCodeService = app(\App\Services\QRCodeService::class);
            
            $shortCode = $codeGenerator->generate();
            
            $link = \App\Models\Link::create([
                'user_id' => $request->user()->id,
                'short_code' => $shortCode,
                'original_url' => $validated['original_url'],
                'custom_alias' => $validated['custom_alias'] ?? null,
                'title' => $validated['title'] ?? null,
            ]);
            
            $qrCodes = $qrCodeService->generate(
                url($link->custom_alias ?? $link->short_code),
                $link->short_code
            );
            
            $link->update([
                'qr_code_png' => $qrCodes['png'],
                'qr_code_svg' => $qrCodes['svg'],
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $link,
                'short_url' => url($link->custom_alias ?? $link->short_code),
            ], 201);
        });
        
        // Get link details
        Route::get('/{link}', function (\App\Models\Link $link) {
            abort_if($link->user_id !== auth()->id(), 403);
            
            return response()->json([
                'success' => true,
                'data' => $link->load('clicks'),
            ]);
        });
        
        // Get link analytics
        Route::get('/{link}/analytics', function (\App\Models\Link $link) {
            abort_if($link->user_id !== auth()->id(), 403);
            
            $analyticsService = app(\App\Services\AnalyticsService::class);
            $analytics = $analyticsService->getLinkAnalytics($link);
            
            return response()->json([
                'success' => true,
                'data' => $analytics,
            ]);
        });
        
        // Delete link
        Route::delete('/{link}', function (\App\Models\Link $link) {
            abort_if($link->user_id !== auth()->id(), 403);
            
            $qrCodeService = app(\App\Services\QRCodeService::class);
            $qrCodeService->delete($link->qr_code_png, $link->qr_code_svg);
            
            $link->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Link deleted successfully',
            ]);
        });
    });
    
});

