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
            $table->string('manage_number')->nullable()->after('product_code');
            $table->string('variant_id')->nullable()->after('manage_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_inventories', function (Blueprint $table) {
            $table->dropColumn('manage_number');
            $table->dropColumn('variant_id');
        });
    }
};
