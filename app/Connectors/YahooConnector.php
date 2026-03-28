<?php

namespace App\Connectors;

use App\Contracts\OAuthConnector;
use App\Models\PlatformConnection;
use App\Models\Shop;
use Illuminate\Http\Request;

/** Stub — to be implemented in a later phase */
class YahooConnector implements OAuthConnector
{
    public function authType(): string
    {
        return 'oauth2';
    }

    public function getAuthUrl(Shop $shop): string
    {
        throw new \RuntimeException('YahooConnector::getAuthUrl not implemented yet.');
    }

    public function handleCallback(Request $request, Shop $shop): PlatformConnection
    {
        throw new \RuntimeException('YahooConnector::handleCallback not implemented yet.');
    }

    public function refreshTokenIfNeeded(PlatformConnection $conn): void {}

    public function fetchOrders(PlatformConnection $conn, array $opts = []): iterable
    {
        return [];
    }

    public function fetchInventory(PlatformConnection $conn, array $opts = []): iterable
    {
        return [];
    }

    public function testConnection(PlatformConnection $conn): bool
    {
        return false;
    }

    public function webhookHandler(Request $request): void {}

    public function fetchOrderItems(PlatformConnection $conn, array $orderIds): iterable
    {
        return [];
    }

    public function normalizeOrderItem(array $raw): array
    {
        return [
            'platform_item_id' => $raw['LineId'] ?? null,
            'product_code'     => $raw['ItemId'] ?? null,
            'product_name'     => $raw['Title'] ?? null,
            'quantity'         => (int) ($raw['Quantity'] ?? 1),
            'unit_price'       => (float) ($raw['UnitPrice'] ?? 0),
            'total_price'      => (float) ($raw['Price'] ?? 0),
        ];
    }

    public function normalizeOrder(array $raw): array
    {
        // Yahoo Shopping field mapping — implement when Yahoo API is wired
        // Reference: https://developer.yahoo.co.jp/webapi/shopping/orders/
        return [
            'platform_order_id'     => $raw['OrderId'] ?? null,
            'platform_order_status' => $raw['OrderStatus'] ?? null,
            'ordered_at'            => $raw['OrderTime'] ?? null,
            'buyer_name'            => $raw['BillLastName'] ?? null,
            'buyer_email'           => $raw['BillMailAddress'] ?? null,
            'buyer_phone'           => $raw['BillPhone1'] ?? null,
            'buyer_zip'             => $raw['BillZipCode'] ?? null,
            'buyer_address'         => trim(($raw['BillPrefecture'] ?? '') . ' ' . ($raw['BillAddress1'] ?? '')) ?: null,
            'delivery_name'         => $raw['ShipLastName'] ?? null,
            'delivery_zip'          => $raw['ShipZipCode'] ?? null,
            'delivery_address'      => trim(($raw['ShipPrefecture'] ?? '') . ' ' . ($raw['ShipAddress1'] ?? '')) ?: null,
            'delivery_method'       => $raw['ShipMethod'] ?? null,
            'payment_method'        => $raw['PayMethod'] ?? null,
            'goods_amount'          => $raw['TotalPrice'] ?? null,
            'delivery_fee'          => $raw['ShipCharge'] ?? null,
            'total_amount'          => $raw['TotalPrice'] ?? null,
        ];
    }

    public function normalizeInventory(array $raw): array
    {
        return [
            'product_code' => $raw['ItemCode'] ?? null,
            'product_name' => $raw['Title'] ?? null,
            'stock'        => $raw['Quantity'] ?? 0,
        ];
    }
}
