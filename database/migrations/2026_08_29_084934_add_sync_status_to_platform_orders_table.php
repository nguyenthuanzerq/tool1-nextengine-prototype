<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('platform_orders', function (Blueprint $table) {
            $table->enum('sync_status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('nextengine_order_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_orders', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'nextengine_order_id']);
        });
    }
};
