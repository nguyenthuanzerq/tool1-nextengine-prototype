<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();

            // Platform-side identifiers
            $table->string('platform_order_id')->index();
            $table->string('platform_order_status')->nullable();

            // Amounts
            $table->decimal('goods_amount', 12, 2)->nullable();
            $table->decimal('delivery_fee', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();

            // Payment
            $table->string('payment_method')->nullable();

            // Buyer info
            $table->string('buyer_id')->nullable();
            $table->string('buyer_name')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('buyer_phone')->nullable();
            $table->string('buyer_zip')->nullable();
            $table->string('buyer_address')->nullable();
            $table->string('customer_type')->nullable();

            // Delivery (recipient — may differ from buyer)
            $table->string('delivery_name')->nullable();
            $table->string('delivery_zip')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('delivery_method')->nullable();

            // Fulfillment
            $table->string('tracking_number')->nullable();
            $table->dateTime('shipped_at')->nullable();

            // Timestamps from platform
            $table->dateTime('ordered_at')->nullable();
            $table->dateTime('synced_at')->nullable();

            // Raw response
            $table->longText('raw_data')->nullable();

            $table->timestamps();

            $table->unique(['platform_id', 'platform_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_orders');
    }
};
