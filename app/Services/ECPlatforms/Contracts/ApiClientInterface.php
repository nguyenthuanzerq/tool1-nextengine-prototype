<?php

namespace App\Services\ECPlatforms\Contracts;

use App\Models\Shop;

interface ApiClientInterface
{
    /**
     * Fetch orders from the platform for the given shop.
     *
     * @param  Shop   $shop    The shop to fetch orders for.
     * @param  array  $params  Optional filter/pagination parameters.
     * @return array  Pre-processed order data.
     */
    public function fetchOrders(Shop $shop, array $params = []): array;

    /**
     * Fetch products from the platform for the given shop.
     */
    public function fetchProducts(Shop $shop, array $params): array;

    /**
     * Update tracking information for an order on the platform.
     */
    public function updateTracking(Shop $shop, string $orderId, string $trackingNumber): bool;
}
