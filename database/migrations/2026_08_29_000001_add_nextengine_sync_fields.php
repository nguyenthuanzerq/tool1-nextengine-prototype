<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_orders', function (Blueprint $table) {
            $table->enum('sync_status', ['pending', 'success', 'failed'])->default('pending')->after('platform_order_status');
            $table->string('nextengine_order_id')->nullable()->after('platform_order_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('nextengine_pattern_id')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('platform_orders', function (Blueprint $table) {
            $table->dropColumn(['sync_status', 'nextengine_order_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nextengine_pattern_id');
        });
    }
};
