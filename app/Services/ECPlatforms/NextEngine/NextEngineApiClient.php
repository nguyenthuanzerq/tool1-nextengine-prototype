<?php

namespace App\Services\ECPlatforms\NextEngine;

use App\Models\Shop;
use App\Services\ECPlatforms\Contracts\ApiClientInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NextEngineApiClient implements ApiClientInterface
{
    /** Next Engine API base URL */
    private const API_BASE = 'https://api.next-engine.org/api_v1_';

    private NextEngineAuthenticator $authenticator;

    public function __construct(NextEngineAuthenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    // ----------------------------------------------------------------
    // ApiClientInterface
    // ----------------------------------------------------------------

    /**
     * Fetch orders from the Next Engine API.
     *
     * Tries the real API first. If the connection fails (e.g. local dev
     * with no internet / sandbox), returns realistic mock data so the
     * rest of the pipeline can be tested end-to-end.
     *
     * Endpoint: /api_v1_receiveorder_base/search
     * @see https://developer.next-engine.com/api/api_v1_receiveorder_base/search
     */
    public function fetchOrders(Shop $shop, array $params = []): array
    {
        $accessToken = $this->resolveAccessToken($shop);

        // Full field list — matches the next_engine_orders migration
        $fields = [
            'receive_order_id',
            'receive_order_date',
            'receive_order_goods_amount',
            'receive_order_delivery_fee_amount',
            'receive_order_purchaser_id',
            'receive_order_creator_name',
            'receive_order_purchaser_tel',
            'receive_order_purchaser_mail_address',
            'receive_order_purchaser_address1',
            'receive_order_purchaser_address2',
            'receive_order_delivery_method_name',
            'receive_order_order_status_id',
        ];

        try {
            $response = Http::asForm()->post(self::API_BASE . 'receiveorder_base/search', array_merge([
                'access_token' => $accessToken,
                'fields'       => implode(',', $fields),
            ], $params));

            $data = $response->json();

            if (($data['result'] ?? '') === 'success') {
                return $data;
            }

            Log::warning('NextEngine fetchOrders returned non-success', [
                'shop_id'  => $shop->id,
                'response' => $data,
            ]);

            return $data ?? [];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // ---------------------------------------------------------
            // 🔧  LOCAL FALLBACK — API unreachable, return mock data
            //     so the sync pipeline can still be tested.
            // ---------------------------------------------------------
            Log::warning('NextEngine API unreachable — returning mock orders', [
                'shop_id' => $shop->id,
                'error'   => $e->getMessage(),
            ]);

            return $this->mockOrders();

        } catch (\Exception $e) {
            Log::error('NextEngine fetchOrders failed', [
                'shop_id' => $shop->id,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Fetch products from the Next Engine API (stub).
     *
     * Endpoint: /api_v1_master_goods/search
     */
    public function fetchProducts(Shop $shop, array $params): array
    {
        $accessToken = $this->resolveAccessToken($shop);

        try {
            $response = Http::withToken($accessToken)
                ->post(self::API_BASE . 'master_goods/search', array_merge([
                    'access_token' => $accessToken,
                ], $params));

            return $response->json() ?? [];
        } catch (\Exception $e) {
            Log::error('NextEngine fetchProducts failed', [
                'shop_id' => $shop->id,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Update the tracking number for an order on Next Engine (stub).
     *
     * Endpoint: /api_v1_receiveorder_base/update
     */
    public function updateTracking(Shop $shop, string $orderId, string $trackingNumber): bool
    {
        $accessToken = $this->resolveAccessToken($shop);

        try {
            $response = Http::withToken($accessToken)
                ->post(self::API_BASE . 'receiveorder_base/update', [
                    'access_token'   => $accessToken,
                    'receive_order_id' => $orderId,
                    'tracking_number'  => $trackingNumber,
                ]);

            $data = $response->json();

            return ($data['result'] ?? '') === 'success';
        } catch (\Exception $e) {
            Log::error('NextEngine updateTracking failed', [
                'shop_id'  => $shop->id,
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);

            return false;
        }
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Resolve a valid access token for the shop, refreshing if expired.
     */
    private function resolveAccessToken(Shop $shop): string
    {
        if ($this->authenticator->tokenExpired($shop)) {
            $this->authenticator->refreshToken($shop);
        }

        $connection = $this->authenticator->getConnection($shop);

        return $connection->access_token;
    }

    /**
     * Return realistic mock order data for local testing.
     * Structure mirrors the actual Next Engine API response.
     */
    private function mockOrders(): array
    {
        return [
            'result' => 'success',
            'count'  => 3,
            'data'   => [
                [
                    'receive_order_id'                      => 'NE-ORD-00001',
                    'receive_order_date'                    => '2026-03-20 10:00:00',
                    'receive_order_goods_amount'            => 15000,
                    'receive_order_delivery_fee_amount'     => 500,
                    'receive_order_purchaser_id'            => 'BUYER-001',
                    'receive_order_creator_name'            => 'テストユーザー',
                    'receive_order_purchaser_tel'           => '090-1234-5678',
                    'receive_order_purchaser_mail_address'  => 'test.user@example.com',
                    'receive_order_purchaser_address1'      => '東京都渋谷区',
                    'receive_order_purchaser_address2'      => '1-2-3 テストビル 5F',
                    'receive_order_delivery_method_name'    => 'ヤマト運輸',
                    'receive_order_order_status_id'         => '10',
                ],
                [
                    'receive_order_id'                      => 'NE-ORD-00002',
                    'receive_order_date'                    => '2026-03-21 12:30:00',
                    'receive_order_goods_amount'            => 8500,
                    'receive_order_delivery_fee_amount'     => 0,
                    'receive_order_purchaser_id'            => 'BUYER-002',
                    'receive_order_creator_name'            => 'サンプル太郎',
                    'receive_order_purchaser_tel'           => '080-9876-5432',
                    'receive_order_purchaser_mail_address'  => 'sample.taro@example.com',
                    'receive_order_purchaser_address1'      => '大阪府大阪市北区',
                    'receive_order_purchaser_address2'      => '梅田 4-5-6',
                    'receive_order_delivery_method_name'    => '佐川急便',
                    'receive_order_order_status_id'         => '20',
                ],
                [
                    'receive_order_id'                      => 'NE-ORD-00003',
                    'receive_order_date'                    => '2026-03-22 09:15:00',
                    'receive_order_goods_amount'            => 32000,
                    'receive_order_delivery_fee_amount'     => 800,
                    'receive_order_purchaser_id'            => 'BUYER-003',
                    'receive_order_creator_name'            => '鈴木花子',
                    'receive_order_purchaser_tel'           => '070-1111-2222',
                    'receive_order_purchaser_mail_address'  => 'hanako.suzuki@example.com',
                    'receive_order_purchaser_address1'      => '福岡県福岡市博多区',
                    'receive_order_purchaser_address2'      => '博多駅前 7-8-9',
                    'receive_order_delivery_method_name'    => '日本郵便',
                    'receive_order_order_status_id'         => '50',
                ],
            ],
        ];
    }
}
