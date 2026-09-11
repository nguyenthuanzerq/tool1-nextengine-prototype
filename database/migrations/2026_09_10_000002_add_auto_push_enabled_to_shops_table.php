<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('shops', 'auto_push_enabled')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->boolean('auto_push_enabled')->default(false)->after('auto_sync_enabled');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('shops', 'auto_push_enabled')) {
            Schema::table('shops', function (Blueprint $table) {
                $table->dropColumn('auto_push_enabled');
            });
        }
    }
};
