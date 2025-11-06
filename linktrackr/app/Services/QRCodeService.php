<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate QR codes (PNG and SVG) for a short URL
     * 
     * @param string $url The short URL to encode
     * @param string $filename Base filename (without extension)
     * @return array ['png' => string, 'svg' => string] Paths to generated files
     */
    public function generate(string $url, string $filename): array
    {
        $pngPath = "qrcodes/{$filename}.png";
        $svgPath = "qrcodes/{$filename}.svg";
        
        try {
            // Generate PNG usando GD (más compatible)
            $pngContent = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->margin(1)
                ->generate($url);
            
            Storage::disk('public')->put($pngPath, $pngContent);
            
        } catch (\Exception $e) {
            \Log::error('QR PNG generation failed: ' . $e->getMessage());
            
            // Fallback: crear QR simple sin opciones avanzadas
            $pngContent = QrCode::format('png')
                ->size(300)
                ->generate($url);
            
            Storage::disk('public')->put($pngPath, $pngContent);
        }
        
        try {
            // Generate SVG (no necesita Imagick)
            $svgContent = QrCode::format('svg')
                ->size(300)
                ->errorCorrection('H')
                ->margin(1)
                ->generate($url);
            
            Storage::disk('public')->put($svgPath, $svgContent);
            
        } catch (\Exception $e) {
            \Log::error('QR SVG generation failed: ' . $e->getMessage());
            
            // Fallback SVG simple
            $svgContent = QrCode::format('svg')
                ->size(300)
                ->generate($url);
            
            Storage::disk('public')->put($svgPath, $svgContent);
        }
        
        return [
            'png' => $pngPath,
            'svg' => $svgPath,
        ];
    }

    /**
     * Generate QR code with custom styling (bonus feature)
     * 
     * @param string $url
     * @param string $filename
     * @param array $options ['color' => string, 'backgroundColor' => string]
     * @return array
     */
    public function generateCustom(string $url, string $filename, array $options = []): array
    {
        $pngPath = "qrcodes/{$filename}.png";
        $svgPath = "qrcodes/{$filename}.svg";
        
        $color = $options['color'] ?? [0, 0, 0];
        $backgroundColor = $options['backgroundColor'] ?? [255, 255, 255];
        
        try {
            // Generate PNG with custom colors
            $pngContent = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->margin(1)
                ->color($color[0], $color[1], $color[2])
                ->backgroundColor($backgroundColor[0], $backgroundColor[1], $backgroundColor[2])
                ->generate($url);
            
            Storage::disk('public')->put($pngPath, $pngContent);
            
        } catch (\Exception $e) {
            \Log::error('Custom QR PNG generation failed: ' . $e->getMessage());
            
            // Fallback sin colores personalizados
            $pngContent = QrCode::format('png')
                ->size(300)
                ->generate($url);
            
            Storage::disk('public')->put($pngPath, $pngContent);
        }
        
        try {
            // Generate SVG with custom colors
            $svgContent = QrCode::format('svg')
                ->size(300)
                ->errorCorrection('H')
                ->margin(1)
                ->color($color[0], $color[1], $color[2])
                ->backgroundColor($backgroundColor[0], $backgroundColor[1], $backgroundColor[2])
                ->generate($url);
            
            Storage::disk('public')->put($svgPath, $svgContent);
            
        } catch (\Exception $e) {
            \Log::error('Custom QR SVG generation failed: ' . $e->getMessage());
            
            // Fallback SVG simple
            $svgContent = QrCode::format('svg')
                ->size(300)
                ->generate($url);
            
            Storage::disk('public')->put($svgPath, $svgContent);
        }
        
        return [
            'png' => $pngPath,
            'svg' => $svgPath,
        ];
    }

    /**
     * Delete QR code files
     * 
     * @param string|null $pngPath
     * @param string|null $svgPath
     * @return void
     */
    public function delete(?string $pngPath, ?string $svgPath): void
    {
        if ($pngPath && Storage::disk('public')->exists($pngPath)) {
            Storage::disk('public')->delete($pngPath);
        }
        
        if ($svgPath && Storage::disk('public')->exists($svgPath)) {
            Storage::disk('public')->delete($svgPath);
        }
    }
}