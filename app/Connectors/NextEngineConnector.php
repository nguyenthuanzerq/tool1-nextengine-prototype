<?php

namespace App\Connectors;

use App\Contracts\OAuthConnector;
use App\Models\Platform;
use App\Models\PlatformConnection;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NextEngineConnector implements OAuthConnector
{
    public function authType(): string
    {
        return 'oauth2';
    }

    // -------------------------------------------------------------------------
    // OAuth2
    // -------------------------------------------------------------------------

    public function getAuthUrl(Shop $shop): string
    {
        $settings   = $this->settings();
        $connection = $this->connection($shop);

        // shop_id is NOT passed in the redirect_uri — it is stored in session by the
        // controller before redirecting, so the callback can read it securely.
        $redirectUri = config('services.next_engine.redirect_uri');

        $query = http_build_query([
            'client_id'    => $connection->client_id,
            'redirect_uri' => $redirectUri,
        ]);

        return $settings['base_uri'] . '/users/sign_in?' . $query;
    }

    public function handleCallback(Request $request, Shop $shop): PlatformConnection
    {
        $settings   = $this->settings();
        $connection = $this->connection($shop);

        $response = Http::withoutVerifying()->asForm()->post(
            $settings['api_uri'] . '/api_neauth',
            [
                'client_id'     => $connection->client_id,
                'client_secret' => $connection->client_secret,
                'uid'           => $request->query('uid'),
                'state'         => $request->query('state'),
            ]
        );

        $data = $response->json();

        $connection->update([
            'access_token'  => $data['access_token']  ?? null,
            'refresh_token' => $data['refresh_token'] ?? null,
        ]);

        return $connection->fresh();
    }

    public function refreshTokenIfNeeded(PlatformConnection $conn): void
    {
        // NextEngine tokens do not use standard expiry — they are refreshed
        // automatically on each API call via access_token + refresh_token pair.
        // No-op here; token refresh is handled inside fetchOrders / fetchInventory.
    }

    // -------------------------------------------------------------------------
    // Data sync
    // -------------------------------------------------------------------------

    public function fetchOrders(PlatformConnection $conn, array $opts = []): iterable
    {
        $settings = $this->settings();

        $response = Http::withoutVerifying()->asForm()->post(
            $settings['api_uri'] . '/api_v1_receiveorder_base/search',
            [
                'access_token'  => $conn->access_token,
                'refresh_token' => $conn->refresh_token,
                'wait_flag'     => 1,
                'fields'        => implode(',', [
                    // Identification
                    'receive_order_id',
                    'receive_order_order_status_id',
                    // Dates
                    'receive_order_date',
                    'receive_order_import_date',
                    'receive_order_last_modified_date',
                    // Buyer (confirmed valid fields)
                    'receive_order_customer_id',
                    'receive_order_creator_name',
                    'receive_order_purchaser_tel',
                    'receive_order_purchaser_mail_address',
                    'receive_order_purchaser_address1',
                    'receive_order_purchaser_address2',
                    'receive_order_customer_type_name',
                    // Delivery
                    'receive_order_delivery_id',
                    'receive_order_delivery_method_name',
                    // Payment & amounts
                    'receive_order_payment_method_name',
                    'receive_order_goods_amount',
                    'receive_order_delivery_fee_amount',
                    // Misc
                    'receive_order_include_possible_order_id',
                    'receive_order_confirm_check_id',
                    'receive_order_confirm_check_name',
                ]),
            ]
        );

        $result = $response->json();

        Log::info('NextEngine fetchOrders response', [
            'result'     => $result['result'] ?? null,
            'count'      => $result['count'] ?? null,
            'data_count' => isset($result['data']) ? count($result['data']) : 0,
            'has_token'  => !empty($conn->access_token),
            'error'      => $result['error'] ?? null,
            'message'    => $result['message'] ?? null,
        ]);

        if (($result['result'] ?? '') !== 'success') {
            Log::warning('NextEngine fetchOrders failed', ['result' => $result]);
            return [];
        }

        // NE returns refreshed tokens in every response — persist them
        $this->updateTokens($conn, $result);

        // Return NE raw rows — caller (SyncService) maps them to PlatformOrder
        return $result['data'] ?? [];
    }

    public function fetchInventory(PlatformConnection $conn, array $opts = []): iterable
    {
        $settings = $this->settings();

        $response = Http::withoutVerifying()->asForm()->post(
            $settings['api_uri'] . '/api_v1_master_stock/search',
            [
                'access_token'  => $conn->access_token,
                'refresh_token' => $conn->refresh_token,
                'wait_flag'     => 1,
                'fields'        => implode(',', [
                    'stock_goods_id',
                    'stock_quantity',
                    'stock_allocation_quantity',
                    'stock_defective_quantity',
                    'stock_remaining_order_quantity',
                    'stock_out_quantity',
                    'stock_free_quantity',
                    'stock_advance_order_quantity',
                    'stock_advance_order_allocation_quantity',
                    'stock_advance_order_free_quantity',
                    'stock_deleted_flag',
                    'stock_creation_date',
                    'stock_last_modified_date',
                    'stock_last_modified_null_safe_date',
                    'stock_creator_id',
                    'stock_creator_name',
                    'stock_last_modified_by_id',
                    'stock_last_modified_by_null_safe_id',
                    'stock_last_modified_by_name',
                    'stock_last_modified_by_null_safe_name',
                ]),
            ]
        );

        $result = $response->json();

        Log::info('NextEngine fetchInventory response', [
            'result'     => $result['result'] ?? null,
            'count'      => $result['count'] ?? null,
            'data_count' => isset($result['data']) ? count($result['data']) : 0,
            'error'      => $result['error'] ?? null,
            'message'    => $result['message'] ?? null,
        ]);

        if (($result['result'] ?? '') !== 'success') {
            Log::warning('NextEngine fetchInventory failed', ['result' => $result]);
            return [];
        }

        // NE returns refreshed tokens in every response — persist them
        $this->updateTokens($conn, $result);

        return $result['data'] ?? [];
    }

    public function fetchRecentInventoryChanges(PlatformConnection $conn): iterable
    {
        $settings = $this->settings();

        $response = Http::withoutVerifying()->asForm()->post(
            $settings['api_uri'] . '/api_v1_master_stock/search',
            [
                'access_token'  => $conn->access_token,
                'refresh_token' => $conn->refresh_token,
                'wait_flag'     => 1,
                // Only get changes from the last 15 minutes
                'stock_last_modified_date-gte' => now()->subMinutes(15)->format('Y-m-d H:i:s'),
                'fields'        => implode(',', [
                    'stock_goods_id',
                    'stock_quantity',
                    'stock_last_modified_date',
                ]),
            ]
        );

        $result = $response->json();
        
        if (($result['result'] ?? '') !== 'success') {
            Log::warning('NextEngine fetchRecentInventoryChanges failed', ['result' => $result]);
            return [];
        }

        $this->updateTokens($conn, $result);
        return $result['data'] ?? [];
    }

    public function updateShipment(PlatformConnection $conn, string $orderId, array $trackingData): void
    {
        throw new \Exception('NextEngine updateShipment is not implemented.');
    }

    public function pushInventory(PlatformConnection $conn, string $sku, int $quantity): bool
    {
        // NextEngine is the Master, we don't push inventory to it from Tool1 in this prototype.
        return false;
    }

    public function fetchOrderItems(PlatformConnection $conn, array $orderIds): iterable
    {
        if (empty($orderIds)) {
            return [];
        }

        $settings = $this->settings();

        $response = Http::withoutVerifying()->asForm()->post(
            $settings['api_uri'] . '/api_v1_receiveorder_row/search',
            [
                'access_token'           => $conn->access_token,
                'refresh_token'          => $conn->refresh_token,
                'wait_flag'              => 1,
                'receive_order_id-in' => implode(',', $orderIds),
                'fields'              => implode(',', [
                    'receive_order_row_shop_cut_form_id',
                    'receive_order_id',
                    'goods_id',
                    'goods_name',
                    'receive_order_row_quantity',
                ]),
            ]
        );

        $result = $response->json();

        Log::info('NextEngine fetchOrderItems response', [
            'result'     => $result['result'] ?? null,
            'count'      => $result['count'] ?? null,
            'data_count' => isset($result['data']) ? count($result['data']) : 0,
            'error'      => $result['error'] ?? null,
        ]);

        if (($result['result'] ?? '') !== 'success') {
            Log::warning('NextEngine fetchOrderItems failed', ['result' => $result]);
            return [];
        }

        $this->updateTokens($conn, $result);

        return $result['data'] ?? [];
    }

    // -------------------------------------------------------------------------
    // Normalizers — map raw NE fields → standard platform_orders fields
    // -------------------------------------------------------------------------

    public function normalizeOrder(array $raw): array
    {
        $goodsAmount    = (float) ($raw['receive_order_goods_amount'] ?? 0);
        $deliveryFee    = (float) ($raw['receive_order_delivery_fee_amount'] ?? 0);

        $deliveryAddress = null; // delivery address fields not available via NE API

        $buyerAddress = trim(
            ($raw['receive_order_purchaser_address1'] ?? '') . ' ' .
                ($raw['receive_order_purchaser_address2'] ?? '')
        ) ?: null;

        return [
            // Identification
            'platform_order_id'     => $raw['receive_order_id'] ?? null,
            'platform_order_status' => (string) ($raw['receive_order_order_status_id'] ?? ''),
            // Date
            'ordered_at'            => $raw['receive_order_date'] ?? null,
            // Buyer
            'buyer_id'              => $raw['receive_order_customer_id'] ?? null,
            'buyer_name'            => $raw['receive_order_creator_name'] ?? null,
            'buyer_email'           => $raw['receive_order_purchaser_mail_address'] ?? null,
            'buyer_phone'           => $raw['receive_order_purchaser_tel'] ?? null,
            'buyer_zip'             => null,
            'buyer_address'         => $buyerAddress,
            'customer_type'         => $raw['receive_order_customer_type_name'] ?? null,
            // Delivery (NE API does not expose delivery address fields on this endpoint)
            'delivery_name'         => null,
            'delivery_zip'          => null,
            'delivery_address'      => null,
            'delivery_method'       => $raw['receive_order_delivery_method_name'] ?? ($raw['receive_order_delivery_id'] ?? null),
            // Payment
            'payment_method'        => $raw['receive_order_payment_method_name'] ?? null,
            // Amounts
            'goods_amount'          => $goodsAmount ?: null,
            'delivery_fee'          => $deliveryFee ?: null,
            'total_amount'          => ($goodsAmount + $deliveryFee) ?: null,
        ];
    }

    public function normalizeOrderItem(array $raw): array
    {
        return [
            'platform_item_id' => $raw['receive_order_row_shop_cut_form_id'] ?? null,
            'product_code'     => $raw['goods_id'] ?? null,
            'product_name'     => $raw['goods_name'] ?? null,
            'quantity'         => (int) ($raw['receive_order_row_quantity'] ?? 1),
            'unit_price'       => 0,
            'total_price'      => 0,
        ];
    }

    public function normalizeInventory(array $raw): array
    {
        // Endpoint: POST /api_v1_master_stock/search
        // stock_goods_id is the only product identifier on this endpoint.
        // product_name is not available here; fetch from /api_v1_master_goods/search if needed.
        return [
            'product_code'    => $raw['stock_goods_id'] ?? null,
            'product_name'    => null,
            'stock'           => (int) ($raw['stock_quantity'] ?? 0),
            'available_stock' => (int) ($raw['stock_free_quantity'] ?? 0),
            'reserved_stock'  => (int) ($raw['stock_allocation_quantity'] ?? 0),
        ];
    }

    // -------------------------------------------------------------------------
    // Misc
    // -------------------------------------------------------------------------

    public function testConnection(PlatformConnection $conn): bool
    {
        if (! $conn->access_token) {
            return false;
        }

        $settings = $this->settings();

        try {
            $response = Http::withoutVerifying()->asForm()->post(
                $settings['api_uri'] . '/api_v1_receiveorder_base/search',
                [
                    'access_token'  => $conn->access_token,
                    'refresh_token' => $conn->refresh_token,
                    'wait_flag'     => 1,
                    'fields'        => 'receive_order_id',
                    'limit'         => 1,
                ]
            );

            return ($response->json()['result'] ?? '') === 'success';
        } catch (\Throwable $e) {
            Log::warning('NextEngineConnector::testConnection error', ['err' => $e->getMessage()]);
            return false;
        }
    }

    public function webhookHandler(Request $request): void
    {
        // NextEngine does not support webhooks — no-op.
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Get platform settings array from the platforms table. */
    private function settings(): array
    {
        $settings = Platform::where('key', 'nextengine')->firstOrFail()->settings;
        if (is_string($settings)) {
            $settings = json_decode($settings, true) ?? [];
        }
        return is_array($settings) ? $settings : [];
    }

    /** Find or create the PlatformConnection row for this shop. */
    private function connection(Shop $shop): PlatformConnection
    {
        $platform = Platform::where('key', 'nextengine')->firstOrFail();

        return PlatformConnection::firstOrNew([
            'platform_id' => $platform->id,
            'shop_id'     => $shop->id,
        ]);
    }

    /**
     * NE returns refreshed tokens in every API response.
     * Always persist them so the next call uses the latest pair.
     */
    private function updateTokens(PlatformConnection $conn, array $result): void
    {
        $newAccess  = $result['access_token']  ?? null;
        $newRefresh = $result['refresh_token'] ?? null;

        if ($newAccess && $newAccess !== $conn->access_token) {
            $conn->access_token  = $newAccess;
            $conn->refresh_token = $newRefresh ?? $conn->refresh_token;
            $conn->save();
        }
    }
}
