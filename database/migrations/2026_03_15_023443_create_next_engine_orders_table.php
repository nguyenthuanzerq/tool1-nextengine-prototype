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
        Schema::create('next_engine_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
                    
            $table->dateTime('receive_order_date')->nullable();
            $table->dateTime('receive_order_import_date')->nullable();
            $table->string('receive_order_delivery_id')->nullable();
            $table->string('receive_order_include_possible_order_id')->nullable();
            $table->string('receive_order_customer_type_name')->nullable();
            $table->string('receive_order_purchaser_address1')->nullable();
            $table->string('receive_order_creator_name')->nullable();
            $table->decimal('receive_order_delivery_fee_amount', 12, 2)->nullable();
            $table->decimal('receive_order_goods_amount', 12, 2)->nullable();
            $table->string('receive_order_purchaser_address2')->nullable();
            $table->string('receive_order_payment_method_name')->nullable();
    
            $table->string('receive_order_id')->index();
            $table->dateTime('receive_order_last_modified_date')->nullable();
    
            $table->string('receive_order_confirm_check_id')->nullable();
            $table->string('receive_order_confirm_ids')->nullable();
            $table->string('receive_order_confirm_check_name')->nullable();
            $table->string('receive_order_order_status_id')->nullable();
            $table->string('tracking_number')->nullable();

            $table->longText('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('next_engine_orders');
    }
};
