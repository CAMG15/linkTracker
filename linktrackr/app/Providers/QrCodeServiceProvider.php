<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Configurar QR Code para usar GD si Imagick no está disponible
        if (!extension_loaded('imagick')) {
            \Log::info('Imagick not available, QR codes will use basic rendering');
        }
    }
}