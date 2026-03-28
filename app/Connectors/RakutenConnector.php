<?php

namespace App\Connectors;

use App\Contracts\ApiKeyConnector;
use App\Models\PlatformConnection;
use Illuminate\Http\Request;

/** Stub — to be implemented in a later phase */
class RakutenConnector implements ApiKeyConnector
{
    public function authType(): string
    {
        return 'api_key';
    }

    public function buildAuthHeaders(PlatformConnection $conn): array
    {
        // Rakuten uses Base64(serviceSecret:licenseKey) Basic auth
        $encoded = base64_encode($conn->client_id . ':' . $conn->client_secret);
        return ['Authorization' => 'ESA ' . $encoded];
    }

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
            'platform_item_id' => $raw['detailNo'] ?? null,
            'product_code'     => $raw['manageNumber'] ?? null,
            'product_name'     => $raw['itemName'] ?? null,
            'quantity'         => (int) ($raw['units'] ?? 1),
            'unit_price'       => (float) ($raw['price'] ?? 0),
            'total_price'      => (float) ($raw['totalPrice'] ?? 0),
        ];
    }

    public function normalizeOrder(array $raw): array
    {
        // Rakuten RMS field mapping — implement when Rakuten API is wired
        // Reference: https://webservice.rms.rakuten.co.jp/menu-r.html
        return [
            'platform_order_id'     => $raw['orderNumber'] ?? null,
            'platform_order_status' => $raw['orderProgress'] ?? null,
            'ordered_at'            => $raw['orderDatetime'] ?? null,
            'buyer_name'            => $raw['ordererName'] ?? null,
            'buyer_email'           => $raw['ordererMailAddress'] ?? null,
            'buyer_phone'           => $raw['ordererTel1'] ?? null,
            'buyer_zip'             => $raw['ordererZipCode'] ?? null,
            'buyer_address'         => trim(($raw['ordererPrefecture'] ?? '') . ' ' . ($raw['ordererAddress1'] ?? '')) ?: null,
            'delivery_name'         => $raw['deliveryName'] ?? null,
            'delivery_zip'          => $raw['deliveryZipCode'] ?? null,
            'delivery_address'      => trim(($raw['deliveryPrefecture'] ?? '') . ' ' . ($raw['deliveryAddress1'] ?? '')) ?: null,
            'delivery_method'       => $raw['shippingMethod'] ?? null,
            'payment_method'        => $raw['paymentMethod'] ?? null,
            'goods_amount'          => $raw['goodsPrice'] ?? null,
            'delivery_fee'          => $raw['deliveryPrice'] ?? null,
            'total_amount'          => $raw['requestPrice'] ?? null,
        ];
    }

    public function normalizeInventory(array $raw): array
    {
        return [
            'product_code' => $raw['manageNumber'] ?? null,
            'product_name' => $raw['itemName'] ?? null,
            'stock'        => $raw['inventoryCount'] ?? 0,
        ];
    }
}
