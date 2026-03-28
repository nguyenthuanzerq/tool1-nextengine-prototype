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
            PlatformSeeder::class,  // 0. Seed platform definitions (nextengine, yahoo, rakuten)
            UserSeeder::class,      // 1. Create Admin account for initial login
        ]);
    }
}
