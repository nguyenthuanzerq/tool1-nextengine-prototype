<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_order_id')->constrained()->cascadeOnDelete();

            $table->string('platform_item_id');
            $table->string('product_code')->nullable();
            $table->string('product_name')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['platform_order_id', 'platform_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_order_items');
    }
};
