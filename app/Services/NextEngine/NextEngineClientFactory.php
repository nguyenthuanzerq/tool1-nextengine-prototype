<?php

namespace App\Services\NextEngine;
use App\Services\NextEngine\NextEngineClient;


use App\Models\Shop;

class NextEngineClientFactory
{
    public function make(Shop $shop): NextEngineClient
    {
        return new NextEngineClient([
            'domain' => $shop->nextengine_domain,
            'client_id' => $shop->client_id,
            'client_secret' => $shop->client_secret,
            'access_token' => $shop->access_token,
            'refresh_token' => $shop->refresh_token,
        ]);
    }
}
