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
        Schema::table('api_traffic_logs', function (Blueprint $table) {
            $table->string('triggered_by_url')->nullable()->after('duration_ms');
            $table->string('triggered_by_route')->nullable()->after('triggered_by_url');
        });

        Schema::table('state_logs', function (Blueprint $table) {
            $table->string('triggered_by_url')->nullable()->after('user_id');
            $table->string('triggered_by_route')->nullable()->after('triggered_by_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs_tables', function (Blueprint $table) {
            //
        });
    }
};
