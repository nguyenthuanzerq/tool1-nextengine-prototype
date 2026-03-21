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
        Schema::create('next_engine_connections', function (Blueprint $table) {
            $table->id();
            $table->string('shop_id');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('api_base_url')->nullable();   // thêm dòng này
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('next_engine_connections');
    }
};
