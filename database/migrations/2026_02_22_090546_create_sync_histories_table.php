<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sync_histories', function (Blueprint $table) {
            $table->id();
            $table->string('sync_code');
            $table->string('shop_name')->nullable();
            $table->string('sync_type');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('status');
            $table->string('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_histories');
    }
};
