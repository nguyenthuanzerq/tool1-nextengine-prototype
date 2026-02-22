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
    Schema::create('inventories', function (Blueprint $table) {
        $table->id();
        $table->string('shop_name');
        $table->string('product_code');
        $table->string('product_name');
        $table->integer('stock');
        $table->integer('available_stock');
        $table->timestamp('last_updated')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
