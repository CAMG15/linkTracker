<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Short code (unique identifier)
            $table->string('short_code', 50)->unique();
            
            // Original destination URL
            $table->text('original_url');
            
            // Optional custom alias
            $table->string('custom_alias', 50)->nullable()->unique();
            
            // QR Code file paths
            $table->string('qr_code_png')->nullable();
            $table->string('qr_code_svg')->nullable();
            
            // Metadata
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            
            // Expiration (bonus feature)
            $table->timestamp('expires_at')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('short_code');
            $table->index('custom_alias');
            $table->index(['user_id', 'created_at']); // For user dashboard queries
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
