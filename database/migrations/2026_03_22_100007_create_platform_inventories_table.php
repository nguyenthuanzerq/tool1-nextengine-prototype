<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();

            $table->string('product_code')->index();
            $table->string('product_name')->nullable();
            $table->string('variant_code')->nullable();

            $table->integer('stock')->default(0);
            $table->integer('available_stock')->default(0);
            $table->integer('reserved_stock')->default(0);

            $table->dateTime('last_synced_at')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['platform_id', 'shop_id', 'product_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_inventories');
    }
};
