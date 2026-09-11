<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('platform_orders', 'sync_status')) {
            Schema::table('platform_orders', function (Blueprint $table) {
                $table->string('sync_status')->default('pending')->after('platform_order_status');
            });
        }

        if (! Schema::hasColumn('platform_orders', 'nextengine_order_id')) {
            Schema::table('platform_orders', function (Blueprint $table) {
                $table->string('nextengine_order_id')->nullable()->after('platform_order_id');
            });
        }

        // Convert the old enum to a string so ignored can be added safely on MySQL.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $column = DB::selectOne("SHOW COLUMNS FROM platform_orders LIKE 'sync_status'");
            if ($column && str_contains(strtolower((string) ($column->Type ?? '')), 'enum')) {
                DB::statement("ALTER TABLE platform_orders MODIFY sync_status VARCHAR(20) NOT NULL DEFAULT 'pending'");
            }
        }

        Schema::table('platform_orders', function (Blueprint $table) {
            $table->index(['shop_id', 'sync_status'], 'platform_orders_shop_sync_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('platform_orders', function (Blueprint $table) {
            $table->dropIndex('platform_orders_shop_sync_status_index');
        });
    }
};
