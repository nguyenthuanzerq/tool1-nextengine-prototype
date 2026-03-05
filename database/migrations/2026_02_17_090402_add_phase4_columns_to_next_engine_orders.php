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
        Schema::table('next_engine_orders', function (Blueprint $table) {
        $table->text('response')->nullable();
        $table->string('system_que_id')->nullable();
        $table->integer('system_que_status_id')->nullable();
        $table->integer('receive_order_order_status_id')->nullable();
        $table->timestamp('last_synced_at')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('next_engine_orders', function (Blueprint $table) {
            //
        });
    }
};
