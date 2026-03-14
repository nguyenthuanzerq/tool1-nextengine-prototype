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
        Schema::table('orders', function (Blueprint $table) {
            // Thêm các cột thiếu từ NextEngine
            $table->string('receipt_receipt_id')->nullable()->after('shop_id');
            $table->dateTime('receive_order_date')->nullable()->after('receipt_receipt_id');
            $table->integer('receive_order_total_amount')->default(0)->after('receive_order_date');
            $table->string('purchaser_name')->nullable()->after('receive_order_total_amount');

            // Đổi tên cột tracking_number thành shipping_delivery_tracking_number
            if (Schema::hasColumn('orders', 'tracking_number')) {
                $table->renameColumn('tracking_number', 'shipping_delivery_tracking_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Xóa các cột vừa thêm nếu rollback
            $table->dropColumn([
                'receipt_receipt_id',
                'receive_order_date',
                'receive_order_total_amount',
                'purchaser_name'
            ]);

            // Đổi tên cột lại như cũ
            if (Schema::hasColumn('orders', 'shipping_delivery_tracking_number')) {
                $table->renameColumn('shipping_delivery_tracking_number', 'tracking_number');
            }
        });
    }
};