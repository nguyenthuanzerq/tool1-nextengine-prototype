<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $orders = [
            ['shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-001', 'receive_order_date' => '2026-03-08 10:00:00', 'receive_order_total_amount' => 15000, 'purchaser_name' => '田中 太郎 (Tanaka Taro)', 'status' => 'pending', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shop_id' => 2, 'receipt_receipt_id' => 'ORD-20260308-002', 'receive_order_date' => '2026-03-07 14:30:00', 'receive_order_total_amount' => 8500, 'purchaser_name' => '鈴木 一郎 (Suzuki Ichiro)', 'status' => 'processing', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-003', 'receive_order_date' => '2026-03-06 09:15:00', 'receive_order_total_amount' => 24000, 'purchaser_name' => '佐藤 花子 (Sato Hanako)', 'status' => 'shipped', 'shipping_delivery_tracking_number' => '1234-5678-9012', 'created_at' => $now, 'updated_at' => $now],
            ['shop_id' => 2, 'receipt_receipt_id' => 'ORD-20260308-004', 'receive_order_date' => '2026-03-08 11:20:00', 'receive_order_total_amount' => 3200, 'purchaser_name' => '高橋 健太 (Takahashi Kenta)', 'status' => 'pending', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-005', 'receive_order_date' => '2026-03-05 16:45:00', 'receive_order_total_amount' => 12800, 'purchaser_name' => '伊藤 美咲 (Ito Misaki)', 'status' => 'shipped', 'shipping_delivery_tracking_number' => '9876-5432-1098', 'created_at' => $now, 'updated_at' => $now],
            ['shop_id' => 2, 'receipt_receipt_id' => 'ORD-20260308-006', 'receive_order_date' => '2026-03-08 08:00:00', 'receive_order_total_amount' => 5400, 'purchaser_name' => '渡辺 大輔 (Watanabe Daisuke)', 'status' => 'pending', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-007', 'receive_order_date' => '2026-03-07 19:10:00', 'receive_order_total_amount' => 19600, 'purchaser_name' => '小林 さくら (Kobayashi Sakura)', 'status' => 'processing', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-008', 'receive_order_date' => '2026-03-04 10:25:00', 'receive_order_total_amount' => 45000, 'purchaser_name' => '加藤 結衣 (Kato Yui)', 'status' => 'shipped', 'shipping_delivery_tracking_number' => '1111-2222-3333', 'created_at' => $now, 'updated_at' => $now],
            ['shop_id' => 2, 'receipt_receipt_id' => 'ORD-20260308-009', 'receive_order_date' => '2026-03-08 12:30:00', 'receive_order_total_amount' => 7600, 'purchaser_name' => '吉田 翔太 (Yoshida Shota)', 'status' => 'pending', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now],
            ['shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-010', 'receive_order_date' => '2026-03-06 15:55:00', 'receive_order_total_amount' => 11200, 'purchaser_name' => '山田 太郎 (Yamada Taro)', 'status' => 'processing', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('orders')->insert($orders);
    }
}
