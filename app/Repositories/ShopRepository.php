<?php

namespace App\Repositories;

use App\Models\Shop;

class ShopRepository
{
    public function findOrFail(int $shopId): Shop
    {
        return Shop::query()->findOrFail($shopId);
    }

    public function getNextEngineConfig(int $shopId): array
    {
        $shop = $this->findOrFail($shopId);

        return [
            'domain' => $shop->nextengine_domain,
            'client_id' => $shop->client_id,
            'client_secret' => $shop->client_secret,
            'access_token' => $shop->access_token,
            'refresh_token' => $shop->refresh_token,
        ];
    }
}
