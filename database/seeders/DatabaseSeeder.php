<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('inventories')->truncate();

        DB::table('inventories')->insert([
            [
                'shop_name'     => '楽天市場店',
                'product_code'  => 'PRD-001',
                'product_name'  => 'テスト商品A',
                'stock_qty'     => 150,
                'available_qty' => 145,
                'updated_at'    => now(),
                'created_at'    => now(),
            ],
            [
                'shop_name'     => 'Amazon店',
                'product_code'  => 'PRD-002',
                'product_name'  => 'テスト商品B',
                'stock_qty'     => 0,      // test zero
                'available_qty' => 0,
                'updated_at'    => now(),
                'created_at'    => now(),
            ],
            [
                'shop_name'     => 'Yahoo店',
                'product_code'  => 'PRD-003',
                'product_name'  => 'テスト商品C',
                'stock_qty'     => 10,
                'available_qty' => 15,     // test lỗi logic
                'updated_at'    => now(),
                'created_at'    => now(),
            ],
        ]);
    }
}