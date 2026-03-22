<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,  // 1. Tạo tài khoản Admin đăng nhập trước
            ShopSeeder::class,  // 2. Tạo Shop (Bảng cha)
            OrderSeeder::class, // 3. Tạo Đơn hàng (Bảng con, cần có Shop trước)
            // InventorySeeder::class, // 4. Tạo dữ liệu tồn kho (Bảng riêng, không phụ thuộc)
        ]);
    }
}
