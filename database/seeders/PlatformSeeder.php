<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = [
            [
                'key'       => 'nextengine',
                'name'      => 'NextEngine',
                'auth_type' => 'oauth2',
                'settings'  => [
                    'base_uri'   => env('NEXT_ENGINE_BASE_URI'),
                    'api_uri'    => env('NEXT_ENGINE_API_URI'),
                    'rate_limit' => 300,
                ],
            ],
            [
                'key'       => 'yahoo',
                'name'      => 'Yahoo Shopping',
                'auth_type' => 'oauth2',
                'settings'  => [
                    'base_uri'   => 'https://circus.shopping.yahooapis.jp',
                    'rate_limit' => 1000,
                ],
            ],
            [
                'key'       => 'rakuten',
                'name'      => 'Rakuten',
                'auth_type' => 'api_key',
                'settings'  => [
                    'base_uri'   => 'https://api.rms.rakuten.co.jp',
                    'rate_limit' => 100,
                ],
            ],
        ];

        foreach ($platforms as $data) {
            Platform::updateOrCreate(['key' => $data['key']], $data);
        }

        $this->command->info('✓ Platforms seeded: nextengine, yahoo, rakuten');
    }
}
