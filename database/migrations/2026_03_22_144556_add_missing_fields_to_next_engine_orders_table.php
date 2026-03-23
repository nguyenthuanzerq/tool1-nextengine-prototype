<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('next_engine_orders', function (Blueprint $table) {
            // Thêm 4 cột còn thiếu (bám sát cách đặt tên của NextEngine)
            $table->string('receive_order_purchaser_id')->nullable()->after('receive_order_customer_type_name')->comment('ID người mua');
            $table->string('receive_order_purchaser_tel')->nullable()->after('receive_order_creator_name')->comment('Số điện thoại KH');
            $table->string('receive_order_purchaser_mail_address')->nullable()->after('receive_order_purchaser_tel')->comment('Email KH');
            $table->string('receive_order_delivery_method_name')->nullable()->after('receive_order_delivery_id')->comment('Tên hãng vận chuyển');
        });
    }

    public function down(): void
    {
        Schema::table('next_engine_orders', function (Blueprint $table) {
            $table->dropColumn([
                'receive_order_purchaser_id',
                'receive_order_purchaser_tel',
                'receive_order_purchaser_mail_address',
                'receive_order_delivery_method_name',
            ]);
        });
    }
};