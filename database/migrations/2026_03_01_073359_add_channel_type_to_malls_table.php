<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('malls', function (Blueprint $table) {
            // yahoo / rakuten / shopify ... (có thể mở rộng)
            $table->string('channel_type', 30)
                ->nullable()
                ->after('mall_name');

            $table->index('channel_type');
        });
    }

    public function down(): void
    {
        Schema::table('malls', function (Blueprint $table) {
            $table->dropIndex(['channel_type']);
            $table->dropColumn('channel_type');
        });
    }
};