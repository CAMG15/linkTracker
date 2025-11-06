<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_id')->constrained()->onDelete('cascade');
            
            // Geographic Data
            $table->string('ip_address', 45)->nullable(); // IPv4 and IPv6
            $table->string('country', 2)->nullable(); // ISO country code
            $table->string('country_name', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            
            // Device & Browser Data
            $table->string('device_type', 20); // mobile, desktop, tablet
            $table->string('browser', 50)->nullable();
            $table->string('browser_version', 20)->nullable();
            $table->string('platform', 50)->nullable(); // Windows, Mac, Linux, iOS, Android
            
            // Referrer Data (where they came from)
            $table->text('referrer')->nullable();
            $table->string('referrer_domain', 255)->nullable();
            
            // QR Code tracking
            $table->boolean('is_qr_code')->default(false);
            
            // User Agent (full string for debugging)
            $table->text('user_agent')->nullable();
            
            // Timestamp
            $table->timestamp('clicked_at')->useCurrent();
            
            // Indexes for analytics queries
            $table->index('link_id');
            $table->index('clicked_at');
            $table->index(['link_id', 'clicked_at']); // Time-series queries
            $table->index('country'); // Geographic reports
            $table->index('device_type'); // Device reports
            $table->index('is_qr_code'); // QR vs Direct tracking
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};