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
        Schema::table('platform_inventories', function (Blueprint $table) {
            $table->string('sync_status')->default('pending')->comment('pending, success, failed');
            $table->timestamp('last_pushed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_inventories', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'last_pushed_at']);
        });
    }
};
