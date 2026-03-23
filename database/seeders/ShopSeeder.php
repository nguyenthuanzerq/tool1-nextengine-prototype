<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // DB::table('shops')->truncate(); 

        DB::table('shops')->insert([
            [
                'ec_platform_id' => 1, 
                'shop_code'      => 'NE-TEST-01',
                'shop_name'      => 'Shop Test NextEngine',
                'status'         => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]
        ]);
    }
}