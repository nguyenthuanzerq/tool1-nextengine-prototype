<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $inventories = [
            [
                'shop_name' => '楽天市場店',
                'product_code' => 'PRD-001',
                'product_name' => 'テスト商品A (Sản phẩm Test A)',
                'stock' => 150,
                'available_stock' => 145,
                'last_updated' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'shop_name' => 'Amazon店',
                'product_code' => 'PRD-002',
                'product_name' => 'テスト商品B (Sản phẩm Test B)',
                'stock' => 0,
                'available_stock' => 0,
                'last_updated' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'shop_name' => 'Yahoo店',
                'product_code' => 'PRD-003',
                'product_name' => 'テスト商品C (Sản phẩm Test C)',
                'stock' => 10,
                'available_stock' => 15,
                'last_updated' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'shop_name' => 'Qoo10店',
                'product_code' => 'PRD-004',
                'product_name' => 'テスト商品D (Sản phẩm Test D)',
                'stock' => 500,
                'available_stock' => 480,
                'last_updated' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('inventories')->insert($inventories);
    }
}
