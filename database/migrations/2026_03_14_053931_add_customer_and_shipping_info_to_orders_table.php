<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('carrier_name')->nullable()->after('shipping_delivery_tracking_number')->comment('Công ty vận chuyển');
            $table->string('purchaser_id')->nullable()->after('purchaser_name')->comment('ID người đặt hàng');
            $table->text('shipping_address')->nullable()->after('purchaser_id')->comment('Địa chỉ người nhận');
            $table->string('purchaser_phone')->nullable()->after('shipping_address')->comment('Số điện thoại người đặt');
            $table->string('purchaser_email')->nullable()->after('purchaser_phone')->comment('Email người đặt');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'carrier_name',
                'purchaser_id',
                'shipping_address',
                'purchaser_phone',
                'purchaser_email',
            ]);
        });
    }
};
