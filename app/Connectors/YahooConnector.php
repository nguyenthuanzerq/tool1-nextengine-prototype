<?php

namespace App\Connectors;

use App\Contracts\OAuthConnector;
use App\Models\PlatformConnection;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YahooConnector implements OAuthConnector
{
    private array $itemCache = [];

    public function authType(): string
    {
        return 'oauth2';
    }

    public function getAuthUrl(Shop $shop): string
    {
        $connection = $this->connection($shop);
        $redirectUri = config('services.yahoo.redirect_uri');
        
        // Use a secure state/nonce
        $nonce = session('yahoo_oauth_nonce') ?? bin2hex(random_bytes(16));
        session(['yahoo_oauth_nonce' => $nonce]);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => $connection->client_id,
            'redirect_uri'  => $redirectUri,
            'scope'         => 'openid profile',
            'state'         => $nonce,
        ]);

        return 'https://auth.login.yahoo.co.jp/yconnect/v2/authorization?' . $query;
    }

    public function handleCallback(Request $request, Shop $shop): PlatformConnection
    {
        $connection = $this->connection($shop);
        $redirectUri = config('services.yahoo.redirect_uri');

        $response = Http::withoutVerifying()->asForm()->post(
            'https://auth.login.yahoo.co.jp/yconnect/v2/token',
            [
                'grant_type'    => 'authorization_code',
                'code'          => $request->query('code'),
                'redirect_uri'  => $redirectUri,
                'client_id'     => $connection->client_id,
                'client_secret' => $connection->client_secret,
            ]
        );

        if ($response->failed()) {
            Log::error('Yahoo callback token exchange failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Failed to exchange authorization code for Yahoo tokens: ' . $response->body());
        }

        $data = $response->json();

        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        $expiresAt = now()->addSeconds($expiresIn - 60);

        $connection->update([
            'access_token'     => $data['access_token'] ?? null,
            'refresh_token'    => $data['refresh_token'] ?? null,
            'token_expires_at' => $expiresAt,
        ]);

        return $connection->fresh();
    }

    public function refreshTokenIfNeeded(PlatformConnection $conn): void
    {
        if (!$conn->access_token || !$conn->refresh_token) {
            return;
        }

        // Refresh if expired or expiring within 5 minutes
        if ($conn->token_expires_at && $conn->token_expires_at->isAfter(now()->addMinutes(5))) {
            return;
        }

        $response = Http::withoutVerifying()->asForm()->post(
            'https://auth.login.yahoo.co.jp/yconnect/v2/token',
            [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $conn->refresh_token,
                'client_id'     => $conn->client_id,
                'client_secret' => $conn->client_secret,
            ]
        );

        if ($response->failed()) {
            Log::error('Yahoo token refresh failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Failed to refresh Yahoo access token: ' . $response->body());
        }

        $data = $response->json();

        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        $expiresAt = now()->addSeconds($expiresIn - 60);

        $conn->update([
            'access_token'     => $data['access_token'] ?? null,
            'refresh_token'    => $data['refresh_token'] ?? $conn->refresh_token,
            'token_expires_at' => $expiresAt,
        ]);
    }

    public function fetchOrders(PlatformConnection $conn, array $opts = []): iterable
    {
        if (empty($conn->seller_id) || empty($conn->access_token)) {
            Log::warning('Yahoo fetchOrders skipped due to missing credentials or seller_id');
            return [];
        }

        // 1. Get active Order IDs via orderList
        $orderListXml = '<?xml version="1.0" encoding="UTF-8"?>' .
            '<Req>' .
            '    <Search>' .
            '        <Result>50</Result>' .
            '        <Start>1</Start>' .
            '        <Sort>+order_time</Sort>' .
            '        <Condition>' .
            '            <IsActive>true</IsActive>' .
            '        </Condition>' .
            '        <Field>OrderId</Field>' .
            '    </Search>' .
            '    <SellerId>' . htmlspecialchars($conn->seller_id, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</SellerId>' .
            '</Req>';

        $response = Http::withoutVerifying()->withToken($conn->access_token)
            ->withHeaders(['Content-Type' => 'application/xml'])
            ->post('https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderList', $orderListXml);

        if ($response->failed()) {
            Log::warning('Yahoo fetchOrders: orderList API failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];
        }

        try {
            $xml = simplexml_load_string($response->body());
        } catch (\Throwable $e) {
            Log::error('Yahoo fetchOrders: Failed to parse orderList response XML', ['error' => $e->getMessage()]);
            return [];
        }

        if (!$xml || $xml->getName() === 'Error') {
            Log::warning('Yahoo fetchOrders: orderList returned error', [
                'code' => $xml ? (string) $xml->Code : 'unknown',
                'msg'  => $xml ? (string) $xml->Message : 'unknown',
            ]);
            return [];
        }

        $orderIds = [];
        if (isset($xml->Result)) {
            foreach ($xml->Result as $result) {
                if (isset($result->OrderId)) {
                    $orderIds[] = (string) $result->OrderId;
                }
            }
        }

        if (empty($orderIds)) {
            return [];
        }

        // 2. Fetch details for each order via orderInfo
        $orders = [];
        $fields = [
            'OrderId', 'OrderStatus', 'OrderTime',
            'BillLastName', 'BillMailAddress', 'BillPhone1', 'BillZipCode', 'BillPrefecture', 'BillAddress1',
            'ShipLastName', 'ShipZipCode', 'ShipPrefecture', 'ShipAddress1', 'ShipMethod',
            'PayMethod', 'TotalPrice', 'ShipCharge', 'Item'
        ];

        foreach ($orderIds as $orderId) {
            $orderInfoXml = '<?xml version="1.0" encoding="UTF-8"?>' .
                '<Req>' .
                '    <Target>' .
                '        <OrderId>' . htmlspecialchars($orderId, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</OrderId>' .
                '        <Field>' . implode(',', $fields) . '</Field>' .
                '    </Target>' .
                '    <SellerId>' . htmlspecialchars($conn->seller_id, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</SellerId>' .
                '</Req>';

            $infoResponse = Http::withoutVerifying()->withToken($conn->access_token)
                ->withHeaders(['Content-Type' => 'application/xml'])
                ->post('https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderInfo', $orderInfoXml);

            if ($infoResponse->failed()) {
                Log::warning("Yahoo fetchOrders: orderInfo failed for {$orderId}", [
                    'status' => $infoResponse->status(),
                ]);
                continue;
            }

            try {
                $infoXml = simplexml_load_string($infoResponse->body());
            } catch (\Throwable $e) {
                Log::warning("Yahoo fetchOrders: Failed to parse orderInfo XML for {$orderId}", ['error' => $e->getMessage()]);
                continue;
            }

            if (!$infoXml || $infoXml->getName() === 'Error') {
                continue;
            }

            if (isset($infoXml->Result)) {
                $resultNode = $infoXml->Result;
                
                // Construct a standard order array representation
                $orderData = [
                    'OrderId'         => (string) $resultNode->OrderId,
                    'OrderStatus'     => (string) $resultNode->OrderStatus,
                    'OrderTime'        => (string) $resultNode->OrderTime,
                    'BillLastName'     => (string) $resultNode->BillLastName,
                    'BillMailAddress'  => (string) $resultNode->BillMailAddress,
                    'BillPhone1'       => (string) $resultNode->BillPhone1,
                    'BillZipCode'      => (string) $resultNode->BillZipCode,
                    'BillPrefecture'   => (string) $resultNode->BillPrefecture,
                    'BillAddress1'     => (string) $resultNode->BillAddress1,
                    'ShipLastName'     => (string) $resultNode->ShipLastName,
                    'ShipZipCode'      => (string) $resultNode->ShipZipCode,
                    'ShipPrefecture'   => (string) $resultNode->ShipPrefecture,
                    'ShipAddress1'     => (string) $resultNode->ShipAddress1,
                    'ShipMethod'       => (string) $resultNode->ShipMethod,
                    'PayMethod'        => (string) $resultNode->PayMethod,
                    'TotalPrice'       => (float) $resultNode->TotalPrice,
                    'ShipCharge'       => (float) $resultNode->ShipCharge,
                ];

                $orders[] = $orderData;

                // Cache order items
                $items = [];
                if (isset($resultNode->Item)) {
                    foreach ($resultNode->Item as $itemNode) {
                        $items[] = [
                            'OrderId'   => (string) $resultNode->OrderId,
                            'LineId'    => (string) $itemNode->LineId,
                            'ItemId'    => (string) $itemNode->ItemId,
                            'Title'     => (string) $itemNode->Title,
                            'Quantity'  => (int) $itemNode->Quantity,
                            'UnitPrice' => (float) $itemNode->UnitPrice,
                            'Price'     => (float) $itemNode->Price,
                        ];
                    }
                }
                $this->itemCache[$orderId] = $items;
            }
        }

        return $orders;
    }

    public function fetchOrderItems(PlatformConnection $conn, array $orderIds): iterable
    {
        $allItems = [];

        foreach ($orderIds as $orderId) {
            // Read from cache if available
            if (isset($this->itemCache[$orderId])) {
                $allItems = array_merge($allItems, $this->itemCache[$orderId]);
                continue;
            }

            // Fallback: fetch dynamically if cache is cold
            if (empty($conn->seller_id) || empty($conn->access_token)) {
                continue;
            }

            $orderInfoXml = '<?xml version="1.0" encoding="UTF-8"?>' .
                '<Req>' .
                '    <Target>' .
                '        <OrderId>' . htmlspecialchars($orderId, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</OrderId>' .
                '        <Field>OrderId,Item</Field>' .
                '    </Target>' .
                '    <SellerId>' . htmlspecialchars($conn->seller_id, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</SellerId>' .
                '</Req>';

            $infoResponse = Http::withoutVerifying()->withToken($conn->access_token)
                ->withHeaders(['Content-Type' => 'application/xml'])
                ->post('https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderInfo', $orderInfoXml);

            if ($infoResponse->failed()) {
                continue;
            }

            try {
                $infoXml = simplexml_load_string($infoResponse->body());
                if ($infoXml && $infoXml->getName() !== 'Error' && isset($infoXml->Result)) {
                    $resultNode = $infoXml->Result;
                    $items = [];
                    if (isset($resultNode->Item)) {
                        foreach ($resultNode->Item as $itemNode) {
                            $items[] = [
                                'OrderId'   => (string) $resultNode->OrderId,
                                'LineId'    => (string) $itemNode->LineId,
                                'ItemId'    => (string) $itemNode->ItemId,
                                'Title'     => (string) $itemNode->Title,
                                'Quantity'  => (int) $itemNode->Quantity,
                                'UnitPrice' => (float) $itemNode->UnitPrice,
                                'Price'     => (float) $itemNode->Price,
                            ];
                        }
                    }
                    $this->itemCache[$orderId] = $items;
                    $allItems = array_merge($allItems, $items);
                }
            } catch (\Throwable $e) {
                Log::warning("Yahoo fetchOrderItems: Failed to parse orderInfo XML for {$orderId}", ['error' => $e->getMessage()]);
            }
        }

        return $allItems;
    }

    public function fetchInventory(PlatformConnection $conn, array $opts = []): iterable
    {
        $skus = $opts['skus'] ?? []; // e.g. [['product_code' => 'A']]
        
        if (empty($skus)) {
            $skus = \App\Models\PlatformInventory::whereHas('platform', function($q) {
                $q->where('key', 'nextengine');
            })->whereNotNull('product_code')->get(['product_code'])->toArray();
        }

        $results = [];
        $this->refreshTokenIfNeeded($conn);

        foreach ($skus as $sku) {
            $productCode = $sku['product_code'] ?? null;
            if (!$productCode) continue;

            $itemCode = $productCode;
            $subCode = null;
            if (strpos($productCode, ':') !== false) {
                list($itemCode, $subCode) = explode(':', $productCode, 2);
            }

            $url = 'https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/getStock';
            $payload = [
                'seller_id' => $conn->seller_id,
                'item_code' => $itemCode,
            ];

            try {
                $response = Http::withoutVerifying()
                    ->withToken($conn->access_token)
                    ->asForm()
                    ->post($url, $payload);

                if ($response->successful()) {
                    $xml = simplexml_load_string($response->body());
                    if ($xml && $xml->getName() !== 'Error') {
                        // Assuming simple XML structure for getStock based on typical Yahoo API
                        $qty = 0;
                        if (isset($xml->Result->Quantity)) {
                            $qty = (int) $xml->Result->Quantity;
                        } elseif (isset($xml->Result->Item->Quantity)) {
                            $qty = (int) $xml->Result->Item->Quantity;
                        }
                        
                        $results[] = [
                            'ItemCode' => $productCode,
                            'Quantity' => $qty,
                        ];
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Yahoo fetchInventory Error for {$productCode}: " . $e->getMessage());
            }
        }

        return $results;
    }

    public function pushInventory(PlatformConnection $conn, string $sku, int $quantity): bool
    {
        throw new \Exception('Yahoo pushInventory is disabled in Read-Only mode.');
    }

    public function updateShipment(PlatformConnection $conn, string $orderId, array $trackingData): void
    {
        throw new \Exception('Yahoo updateShipment is not implemented yet.');
    }

    public function testConnection(PlatformConnection $conn): bool
    {
        if (!$conn->access_token || !$conn->seller_id) {
            return false;
        }

        try {
            $this->refreshTokenIfNeeded($conn);

            $response = Http::withoutVerifying()->withToken($conn->access_token)
                ->get('https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderCount', [
                    'sellerId' => $conn->seller_id,
                ]);

            if ($response->failed()) {
                return false;
            }

            $xml = simplexml_load_string($response->body());
            return $xml && $xml->getName() !== 'Error';
        } catch (\Throwable $e) {
            Log::warning('YahooConnector::testConnection error', ['err' => $e->getMessage()]);
            return false;
        }
    }

    public function webhookHandler(Request $request): void {}

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
            'goods_amount'          => $raw['TotalPrice'] - $raw['ShipCharge'],
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

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function settings(): array
    {
        $platform = \App\Models\Platform::where('key', 'yahoo')->first();
        $settings = $platform ? $platform->settings : [];
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }
        return is_array($settings) ? $settings : [];
    }

    private function connection(Shop $shop): PlatformConnection
    {
        $platform = \App\Models\Platform::where('key', 'yahoo')->firstOrFail();

        return PlatformConnection::firstOrNew([
            'platform_id' => $platform->id,
            'shop_id'     => $shop->id,
        ]);
    }
}
