<?php

namespace App\Connectors;

use App\Contracts\OAuthConnector;
use App\Models\PlatformConnection;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyConnector implements OAuthConnector
{
    private array $itemCache = [];

    public function authType(): string
    {
        return 'oauth2';
    }

    public function getAuthUrl(Shop $shop): string
    {
        $connection = $this->connection($shop);
        $redirectUri = url('/shopify/callback');
        
        $settings = $shop->platform->settings;
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }
        $scopes = $settings['scopes'] ?? 'read_orders,write_inventory,read_all_orders';
        
        $nonce = session('shopify_oauth_nonce') ?? bin2hex(random_bytes(16));
        session(['shopify_oauth_nonce' => $nonce]);

        $shopUrl = $connection->seller_id;
        if (!$shopUrl) {
            throw new \RuntimeException('Shop URL is not provided.');
        }

        $shopUrl = preg_replace('#^https?://#', '', rtrim($shopUrl, '/'));

        $query = http_build_query([
            'client_id'    => $connection->client_id,
            'scope'        => $scopes,
            'redirect_uri' => $redirectUri,
            'state'        => $nonce,
        ]);

        return "https://{$shopUrl}/admin/oauth/authorize?" . $query;
    }

    public function handleCallback(Request $request, Shop $shop): PlatformConnection
    {
        $connection = $this->connection($shop);
        $code = $request->query('code');
        $shopUrl = $request->query('shop');

        if (!$shopUrl) {
            $shopUrl = $connection->seller_id;
        }

        $response = Http::withoutVerifying()->post("https://{$shopUrl}/admin/oauth/access_token", [
            'client_id'     => $connection->client_id,
            'client_secret' => $connection->client_secret,
            'code'          => $code,
        ]);

        if ($response->failed()) {
            Log::error('Shopify callback token exchange failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Failed to exchange authorization code for Shopify tokens: ' . $response->body());
        }

        $data = $response->json();
        
        $connection->update([
            'access_token'  => $data['access_token'] ?? null,
            'seller_id'     => $shopUrl,
        ]);

        return $connection->fresh();
    }

    public function refreshTokenIfNeeded(PlatformConnection $conn): void
    {
        // No-op for offline tokens
    }

    public function graphqlRequest(PlatformConnection $conn, string $query, array $variables = []): array
    {
        $shopUrl = $conn->seller_id;
        
        $platform = \App\Models\Platform::find($conn->platform_id);
        $settings = $platform ? $platform->settings : [];
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }
        $apiVersion = $settings['api_version'] ?? '2024-04';
        
        $endpoint = "https://{$shopUrl}/admin/api/{$apiVersion}/graphql.json";

        $payload = ['query' => $query];
        if (!empty($variables)) {
            $payload['variables'] = $variables;
        }
        $response = Http::withoutVerifying()
            ->withHeaders([
                'X-Shopify-Access-Token' => $conn->access_token,
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, $payload);

        if ($response->failed()) {
            Log::error('Shopify GraphQL request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'query' => $query,
            ]);
            throw new \RuntimeException('Shopify API Error: ' . $response->body());
        }

        $json = $response->json();

        if (isset($json['errors'])) {
            Log::error('Shopify GraphQL errors', [
                'errors' => $json['errors'],
                'query' => $query,
                'variables' => $variables,
            ]);
            throw new \RuntimeException('Shopify GraphQL Error: ' . json_encode($json['errors']));
        }

        return $json;
    }

    public function fetchOrders(PlatformConnection $conn, array $opts = []): iterable
    {
        if (empty($conn->access_token) || empty($conn->seller_id)) {
            return [];
        }
        $query = '
            query {
                orders(first: 50, sortKey: CREATED_AT, reverse: true) {
                    edges {
                        node {
                            id
                            name
                            displayFulfillmentStatus
                            createdAt
                            totalPriceSet { shopMoney { amount } }
                            totalShippingPriceSet { shopMoney { amount } }
                            subtotalPriceSet { shopMoney { amount } }
                            shippingAddress {
                                firstName
                                lastName
                                zip
                                province
                                city
                                address1
                                address2
                                phone
                            }
                            billingAddress {
                                firstName
                                lastName
                                zip
                                province
                                city
                                address1
                                address2
                                phone
                            }
                            email
                            lineItems(first: 50) {
                                edges {
                                    node {
                                        id
                                        title
                                        quantity
                                        sku
                                        originalTotalSet { shopMoney { amount } }
                                        originalUnitPriceSet { shopMoney { amount } }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        ';
        
        $data = $this->graphqlRequest($conn, $query);
        
        if (isset($data['errors'])) {
            throw new \RuntimeException("Shopify API Error (Orders): " . json_encode($data['errors']));
        }
        $edges = $data['data']['orders']['edges'] ?? [];
        $orders = [];
        foreach ($edges as $edge) {
            $node = $edge['node'];
            
            $lineItems = [];
            if (isset($node['lineItems']['edges'])) {
                foreach ($node['lineItems']['edges'] as $itemEdge) {
                    $itemEdge['node']['_order_id'] = $node['id'];
                    $lineItems[] = $itemEdge['node'];
                }
            }
            $this->itemCache[$node['id']] = $lineItems;
            $orders[] = $node;
        }
        return $orders;
    }

    public function fetchOrderItems(PlatformConnection $conn, array $orderIds): iterable
    {
        $allItems = [];
        foreach ($orderIds as $orderId) {
            if (isset($this->itemCache[$orderId])) {
                $allItems = array_merge($allItems, $this->itemCache[$orderId]);
            }
        }
        return $allItems;
    }

    public function fetchInventory(PlatformConnection $conn, array $opts = []): iterable
    {
        if (empty($conn->access_token) || empty($conn->seller_id)) {
            return [];
        }
        $query = '
            query {
                inventoryItems(first: 50) {
                    edges {
                        node {
                            id
                            sku
                            tracked
                            inventoryLevels(first: 10) {
                                edges {
                                    node {
                                        quantities(names: ["available"]) { name quantity }
                                        location { id name }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        ';
        $data = $this->graphqlRequest($conn, $query);
        if (isset($data['errors'])) {
            throw new \RuntimeException("Shopify API Error (Inventory): " . json_encode($data['errors']));
        }
        $edges = $data['data']['inventoryItems']['edges'] ?? [];
        $items = [];
        foreach ($edges as $edge) {
            $items[] = $edge['node'];
        }        
        return $items;
    }

    public function normalizeOrder(array $raw): array
    {
        $billing = $raw['billingAddress'] ?? [];
        $shipping = $raw['shippingAddress'] ?? [];
        
        $billingName = trim(($billing['firstName'] ?? '') . ' ' . ($billing['lastName'] ?? ''));
        $shippingName = trim(($shipping['firstName'] ?? '') . ' ' . ($shipping['lastName'] ?? ''));
        
        $billingAddress = trim(($billing['province'] ?? '') . ' ' . ($billing['city'] ?? '') . ' ' . ($billing['address1'] ?? '') . ' ' . ($billing['address2'] ?? ''));
        $shippingAddress = trim(($shipping['province'] ?? '') . ' ' . ($shipping['city'] ?? '') . ' ' . ($shipping['address1'] ?? '') . ' ' . ($shipping['address2'] ?? ''));

        return [
            'platform_order_id'     => $raw['id'] ?? null,
            'platform_order_status' => $raw['displayFulfillmentStatus'] ?? null,
            'ordered_at'            => $raw['createdAt'] ?? null,
            'buyer_name'            => $billingName ?: null,
            'buyer_email'           => $raw['email'] ?? null,
            'buyer_phone'           => $billing['phone'] ?? null,
            'buyer_zip'             => $billing['zip'] ?? null,
            'buyer_address'         => $billingAddress ?: null,
            'delivery_name'         => $shippingName ?: null,
            'delivery_zip'          => $shipping['zip'] ?? null,
            'delivery_address'      => $shippingAddress ?: null,
            'delivery_method'       => null,
            'payment_method'        => null,
            'goods_amount'          => $raw['subtotalPriceSet']['shopMoney']['amount'] ?? 0,
            'delivery_fee'          => $raw['totalShippingPriceSet']['shopMoney']['amount'] ?? 0,
            'total_amount'          => $raw['totalPriceSet']['shopMoney']['amount'] ?? 0,
        ];
    }

    public function normalizeOrderItem(array $raw): array
    {
        return [
            'platform_item_id' => $raw['id'] ?? null,
            'product_code'     => $raw['sku'] ?? null,
            'product_name'     => $raw['title'] ?? null,
            'quantity'         => (int) ($raw['quantity'] ?? 1),
            'unit_price'       => (float) ($raw['originalUnitPriceSet']['shopMoney']['amount'] ?? 0),
            'total_price'      => (float) ($raw['originalTotalSet']['shopMoney']['amount'] ?? 0),
        ];
    }

    public function normalizeInventory(array $raw): array
    {
        $totalQuantity = 0;
        $levels = $raw['inventoryLevels']['edges'] ?? [];
        foreach ($levels as $level) {
            $quantities = $level['node']['quantities'] ?? [];
            foreach ($quantities as $qty) {
                if (($qty['name'] ?? '') === 'available') {
                    $totalQuantity += (int) ($qty['quantity'] ?? 0);
                }
            }
        }

        return [
            'product_code' => $raw['sku'] ?? null,
            'product_name' => $raw['sku'] ?? null,
            'stock'        => $totalQuantity,
        ];
    }

    public function updateShipment(PlatformConnection $conn, string $orderId, array $trackingData): void
    {
        if (empty($conn->access_token) || empty($conn->seller_id)) {
            return;
        }
        $trackingNumber = $trackingData['tracking_number'] ?? '';
        $carrier = $trackingData['carrier'] ?? '';
        // 1. Query GraphQL for current Fulfillment data of this Order
        $fetchQuery = '
            query getFulfillmentData($id: ID!) {
                order(id: $id) {
                    fulfillmentOrders(first: 5) {
                        edges {
                            node {
                                id
                                status
                            }
                        }
                    }
                    fulfillments(first: 5) {
                        edges {
                            node {
                                id
                            }
                        }
                    }
                }
            }
        ';
        
        $data = $this->graphqlRequest($conn, $fetchQuery, ['id' => $orderId]);
        
        if (isset($data['errors'])) {
            Log::error('Shopify updateShipment getFulfillmentData error', ['errors' => $data['errors']]);
            return;
        }
        $orderNode = $data['data']['order'] ?? null;
        if (!$orderNode) return;
        $fulfillments = $orderNode['fulfillments']['edges'] ?? [];
        $fulfillmentOrders = $orderNode['fulfillmentOrders']['edges'] ?? [];
        // 2. Decide whether to CREATE or UPDATE tracking
        if (!empty($fulfillments)) {
            // CASE A: Order is ALREADY FULFILLED, just update tracking info
            $fulfillmentId = $fulfillments[0]['node']['id'];
            $mutation = '
                mutation fulfillmentTrackingInfoUpdate($fulfillmentId: ID!, $trackingInfoInput: FulfillmentTrackingInput!) {
                    fulfillmentTrackingInfoUpdate(fulfillmentId: $fulfillmentId, trackingInfoInput: $trackingInfoInput) {
                        fulfillment { id }
                        userErrors { field message }
                    }
                }
            ';
            $response = $this->graphqlRequest($conn, $mutation, [
                'fulfillmentId' => $fulfillmentId,
                'trackingInfoInput' => [
                    'number' => $trackingNumber,
                    'company' => $carrier,
                ]
            ]);
            $userErrors = $response['data']['fulfillmentTrackingInfoUpdate']['userErrors'] ?? [];
            if (!empty($userErrors)) {
                throw new \RuntimeException("Shopify từ chối sửa vận đơn: " . json_encode($userErrors));
            }
        } else {
            // CASE B: Order is UNFULFILLED, use fulfillmentCreateV2 API
            $fulfillmentOrderId = null;
            foreach ($fulfillmentOrders as $foEdge) {
                if (in_array($foEdge['node']['status'], ['OPEN', 'IN_PROGRESS'])) {
                    $fulfillmentOrderId = $foEdge['node']['id'];
                    break;
                }
            }
            if (!$fulfillmentOrderId) {
                Log::warning('Shopify updateShipment: No OPEN FulfillmentOrder found for ' . $orderId);
                return;
            }
            $mutation = '
                mutation fulfillmentCreateV2($fulfillment: FulfillmentV2Input!) {
                    fulfillmentCreateV2(fulfillment: $fulfillment) {
                        fulfillment { id }
                        userErrors { field message }
                    }
                }
            ';
            $response = $this->graphqlRequest($conn, $mutation, [
                'fulfillment' => [
                    'lineItemsByFulfillmentOrder' => [
                        [ 'fulfillmentOrderId' => $fulfillmentOrderId ]
                    ],
                    'trackingInfo' => [
                        'number' => $trackingNumber,
                        'company' => $carrier,
                    ]
                ]
            ]);
            $userErrors = $response['data']['fulfillmentCreateV2']['userErrors'] ?? [];
            if (!empty($userErrors)) {
                throw new \RuntimeException("Shopify từ chối tạo vận đơn: " . json_encode($userErrors));
            }
        }
    }

    public function testConnection(PlatformConnection $conn): bool
    {
        if (!$conn->access_token || !$conn->seller_id) {
            return false;
        }
        
        try {
            $query = 'query { shop { id name } }';
            $data = $this->graphqlRequest($conn, $query);
            return isset($data['data']['shop']['id']);
        } catch (\Throwable $e) {
            Log::warning('Shopify testConnection error', ['err' => $e->getMessage()]);
            return false;
        }
    }

    public function webhookHandler(Request $request): void
    {
        // No-op
    }

    public function pushInventory(PlatformConnection $conn, string $sku, int $quantity): bool
    {
        // TODO: Implement pushInventory via GraphQL inventoryAdjustQuantities or inventorySetOnHandQuantities
        Log::info('Shopify pushInventory stub called', ['sku' => $sku, 'qty' => $quantity]);
        return false;
    }

    private function connection(Shop $shop): PlatformConnection
    {
        $platform = \App\Models\Platform::where('key', 'shopify')->firstOrFail();

        return PlatformConnection::firstOrNew([
            'platform_id' => $platform->id,
            'shop_id'     => $shop->id,
        ]);
    }
}
