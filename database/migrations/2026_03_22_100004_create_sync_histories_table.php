<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('platform_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sync_code');
            $table->string('shop_name')->nullable();   // denormalized for display after shop deletion
            $table->string('sync_type');               // 'orders' | 'inventory'
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('status');                  // 'running' | 'success' | 'failed'
            $table->string('error_message')->nullable();
            $table->json('meta')->nullable();          // synced_count, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_histories');
    }
};
