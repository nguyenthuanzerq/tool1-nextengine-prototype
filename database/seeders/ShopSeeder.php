<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        DB::table('shops')->insert([
            [
            
                'shop_code' => 'SHOP-01', 
                'shop_name' => '楽天市場店 (Rakuten)', 
                'status' => 1, 
                'created_at' => $now, 
                'updated_at' => $now
            ],
            [
                 
                'shop_code' => 'SHOP-02', 
                'shop_name' => 'Amazon店 (Amazon)', 
                'status' => 1, 
                'created_at' => $now, 
                'updated_at' => $now
            ],
        ]);
    }
}