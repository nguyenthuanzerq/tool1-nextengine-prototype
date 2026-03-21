<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $orders = [
            [
                'shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-001', 'receive_order_date' => '2026-03-08 10:00:00', 'receive_order_total_amount' => 15000,
                'purchaser_id' => 'CUST-4521', 'purchaser_name' => '田中 太郎 (Tanaka Taro)', 'purchaser_phone' => '090-1234-5678', 'purchaser_email' => 'tanaka.taro@example.com', 'shipping_address' => '東京都渋谷区神南1-2-3 渋谷マンション101',
                'carrier_name' => 'ヤマト運輸', 'status' => 'pending', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'shop_id' => 2, 'receipt_receipt_id' => 'ORD-20260308-002', 'receive_order_date' => '2026-03-07 14:30:00', 'receive_order_total_amount' => 8500,
                'purchaser_id' => 'CUST-2100', 'purchaser_name' => '鈴木 一郎 (Suzuki Ichiro)', 'purchaser_phone' => '080-1111-2222', 'purchaser_email' => 'suzuki.ichiro@example.com', 'shipping_address' => '神奈川県横浜市中区山下町1-1 ハーバーマンション305',
                'carrier_name' => '日本郵便', 'status' => 'processing', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-003', 'receive_order_date' => '2026-03-06 09:15:00', 'receive_order_total_amount' => 24000,
                'purchaser_id' => 'CUST-7032', 'purchaser_name' => '佐藤 花子 (Sato Hanako)', 'purchaser_phone' => '050-9876-5432', 'purchaser_email' => 'sato.hanako@example.com', 'shipping_address' => '大阪府大阪市北区梅田2-5-10 梅田ビル502',
                'carrier_name' => '佐川急便', 'status' => 'shipped', 'shipping_delivery_tracking_number' => '1234-5678-9012', 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'shop_id' => 2, 'receipt_receipt_id' => 'ORD-20260308-004', 'receive_order_date' => '2026-03-08 11:20:00', 'receive_order_total_amount' => 3200,
                'purchaser_id' => 'CUST-9451', 'purchaser_name' => '高橋 健太 (Takahashi Kenta)', 'purchaser_phone' => '090-3333-4444', 'purchaser_email' => 'takahashi.kenta@example.com', 'shipping_address' => '愛知県名古屋市中村区名駅3-4-5 名駅タワー1202',
                'carrier_name' => 'ヤマト運輸', 'status' => 'pending', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-005', 'receive_order_date' => '2026-03-05 16:45:00', 'receive_order_total_amount' => 12800,
                'purchaser_id' => 'CUST-6723', 'purchaser_name' => '伊藤 美咲 (Ito Misaki)', 'purchaser_phone' => '090-5555-6666', 'purchaser_email' => 'ito.misaki@example.com', 'shipping_address' => '福岡県福岡市博多区博多駅前2-3-4 博多センタービル801',
                'carrier_name' => '佐川急便', 'status' => 'shipped', 'shipping_delivery_tracking_number' => '9876-5432-1098', 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'shop_id' => 2, 'receipt_receipt_id' => 'ORD-20260308-006', 'receive_order_date' => '2026-03-08 08:00:00', 'receive_order_total_amount' => 5400,
                'purchaser_id' => 'CUST-1029', 'purchaser_name' => '渡辺 大輔 (Watanabe Daisuke)', 'purchaser_phone' => '080-7777-8888', 'purchaser_email' => 'watanabe.d@example.com', 'shipping_address' => '北海道札幌市中央区大通西1-2-3 札幌ハイツ401',
                'carrier_name' => 'ヤマト運輸', 'status' => 'pending', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-007', 'receive_order_date' => '2026-03-07 19:10:00', 'receive_order_total_amount' => 19600,
                'purchaser_id' => 'CUST-3344', 'purchaser_name' => '小林 さくら (Kobayashi Sakura)', 'purchaser_phone' => '090-9999-0000', 'purchaser_email' => 'kobayashi.s@example.com', 'shipping_address' => '京都府京都市中京区河原町通三条上ル恵比須町4-5',
                'carrier_name' => '日本郵便', 'status' => 'processing', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-008', 'receive_order_date' => '2026-03-04 10:25:00', 'receive_order_total_amount' => 45000,
                'purchaser_id' => 'CUST-8812', 'purchaser_name' => '加藤 結衣 (Kato Yui)', 'purchaser_phone' => '050-1234-9876', 'purchaser_email' => 'kato.yui@example.com', 'shipping_address' => '兵庫県神戸市中央区三宮町1-2-3 神戸ポートタワーマンション205',
                'carrier_name' => '佐川急便', 'status' => 'shipped', 'shipping_delivery_tracking_number' => '1111-2222-3333', 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'shop_id' => 2, 'receipt_receipt_id' => 'ORD-20260308-009', 'receive_order_date' => '2026-03-08 12:30:00', 'receive_order_total_amount' => 7600,
                'purchaser_id' => 'CUST-5566', 'purchaser_name' => '吉田 翔太 (Yoshida Shota)', 'purchaser_phone' => '080-2345-6789', 'purchaser_email' => 'yoshida.s@example.com', 'shipping_address' => '宮城県仙台市青葉区国分町2-3-4 仙台レジデンス603',
                'carrier_name' => 'ヤマト運輸', 'status' => 'pending', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'shop_id' => 1, 'receipt_receipt_id' => 'ORD-20260308-010', 'receive_order_date' => '2026-03-06 15:55:00', 'receive_order_total_amount' => 11200,
                'purchaser_id' => 'CUST-2233', 'purchaser_name' => '山田 太郎 (Yamada Taro)', 'purchaser_phone' => '090-8765-4321', 'purchaser_email' => 'yamada.taro@example.com', 'shipping_address' => '広島県広島市中区八丁堀1-2-3 広島プラザ302',
                'carrier_name' => '日本郵便', 'status' => 'processing', 'shipping_delivery_tracking_number' => null, 'created_at' => $now, 'updated_at' => $now,
            ],
        ];

        DB::table('orders')->insert($orders);
    }
}
