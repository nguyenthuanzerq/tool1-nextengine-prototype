<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();

            // Credentials — stored encrypted via model accessors (Crypt::encryptString)
            $table->text('client_id')->nullable();
            $table->text('client_secret')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();

            // Non-sensitive platform config (API base URLs, scopes, etc.) — not encrypted
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->unique(['platform_id', 'shop_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_connections');
    }
};
