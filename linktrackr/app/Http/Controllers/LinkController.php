<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\ShortCodeGenerator;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LinkController extends Controller
{
     private ShortCodeGenerator $codeGenerator;
    private QRCodeService $qrCodeService;

    public function __construct(
        ShortCodeGenerator $codeGenerator,
        QRCodeService $qrCodeService
    ) {
        $this->codeGenerator = $codeGenerator;
        $this->qrCodeService = $qrCodeService;
        
        $this->middleware('auth');
    }

    /**
     * Display user's dashboard with their links
     */
    public function index()
    {
        $links = Link::where('user_id', Auth::id())
            ->withCount('clicks')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('dashboard', compact('links'));
    }

    /**
     * Show the form for creating a new link
     */
    public function create()
    {
        return view('links.create');
    }

    /**
     * Store a newly created link
     */
    public function store(Request $request)
    {
        $request->validate([
            'original_url' => [
                'required',
                'url',
                'max:2048',
                'regex:/^https?:\/\/.+/',
            ],
            'custom_alias' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9-]+$/',
                'unique:links,custom_alias',
                'unique:links,short_code',
            ],
            'title' => 'nullable|string|max:255',
        ]);

        // Validate custom alias if provided
        if ($request->filled('custom_alias')) {
            $validation = $this->codeGenerator->validateCustomAlias($request->custom_alias);
            
            if (!$validation['valid']) {
                throw ValidationException::withMessages([
                    'custom_alias' => [$validation['error']]
                ]);
            }
        }

        // Generate short code
        $shortCode = $this->codeGenerator->generate();

        // Create link
        $link = Link::create([
            'user_id' => Auth::id(),
            'short_code' => $shortCode,
            'original_url' => $request->original_url,
            'custom_alias' => $request->custom_alias,
            'title' => $request->title,
        ]);

        // Generate QR codes
       // Generate QR codes (solo SVG por compatibilidad)
try {
    $shortUrl = url($link->custom_alias ?? $link->short_code);
    
    // SVG (siempre funciona)
    $svgContent = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
        ->size(300)
        ->generate($shortUrl);
    
    $svgPath = "qrcodes/{$link->short_code}.svg";
    \Illuminate\Support\Facades\Storage::disk('public')->put($svgPath, $svgContent);
    
    // PNG - intentar con manejo de errores
    try {
        $pngContent = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
            ->size(300)
            ->generate($shortUrl);
        
        $pngPath = "qrcodes/{$link->short_code}.png";
        \Illuminate\Support\Facades\Storage::disk('public')->put($pngPath, $pngContent);
    } catch (\Exception $e) {
        // Si PNG falla, usar el SVG como PNG también
        $pngPath = $svgPath;
        \Log::warning('PNG QR generation failed, using SVG: ' . $e->getMessage());
    }
    
        $link->update([
            'qr_code_png' => $pngPath,
            'qr_code_svg' => $svgPath,
        ]);
        
    } catch (\Exception $e) {
        \Log::error('QR code generation failed: ' . $e->getMessage());
        // Continuar sin QR codes
    }

        // Update QR paths if they were generated
        $update = [];
        if (isset($pngPath)) {
            $update['qr_code_png'] = $pngPath;
        }
        if (isset($svgPath)) {
            $update['qr_code_svg'] = $svgPath;
        }
        if (!empty($update)) {
            $link->update($update);
        }

        return redirect()
            ->route('links.show', $link)
            ->with('success', 'Short link created successfully!');
    }

    /**
     * Display the specified link with analytics
     */
    public function show(Link $link)
    {
        // Ensure user owns this link
        $this->authorize('view', $link);

        $link->loadCount([
            'clicks',
            'clicks as qr_clicks' => function ($query) {
                $query->where('is_qr_code', true);
            },
        ]);

        // Get recent clicks
        $recentClicks = $link->clicks()
            ->latest('clicked_at')
            ->limit(10)
            ->get();

        return view('links.show', compact('link', 'recentClicks'));
    }

    /**
     * Update the specified link
     */
    public function update(Request $request, Link $link)
    {
        $this->authorize('update', $link);

        $request->validate([
            'title' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $link->update($request->only(['title', 'is_active']));

        return back()->with('success', 'Link updated successfully!');
    }

    /**
     * Remove the specified link
     */
    public function destroy(Link $link)
    {
        $this->authorize('delete', $link);

        // Delete QR code files
        $this->qrCodeService->delete($link->qr_code_png, $link->qr_code_svg);

        $link->delete();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Link deleted successfully!');
    }

    /**
     * Download QR code
     */
    public function downloadQr(Link $link, string $format)
    {
        $this->authorize('view', $link);

        $path = $format === 'svg' ? $link->qr_code_svg : $link->qr_code_png;
        $filename = ($link->custom_alias ?? $link->short_code) . '.' . $format;

        return response()->download(
            storage_path('app/public/' . $path),
            $filename
        );
    }
}
