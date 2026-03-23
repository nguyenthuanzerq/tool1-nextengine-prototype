<?php

namespace App\Repositories\NextEngine;

use App\Models\NextEngineOrder;
use Illuminate\Support\Facades\Log;

class NextEngineOrderRepository
{
    /**
     * Persist a single order record from the NE API response.
     *
     * Uses updateOrCreate keyed on (shop_id + receive_order_id) so
     * re-syncing the same order just updates the existing row.
     *
     * @param  int   $shopId   The shop this order belongs to.
     * @param  array $apiData  A single order item from the API 'data' array.
     * @return NextEngineOrder
     */
    public function updateOrCreateFromApi(int $shopId, array $apiData): NextEngineOrder
    {
        return NextEngineOrder::updateOrCreate(
            // ---------- Unique key ----------
            [
                'shop_id'          => $shopId,
                'receive_order_id' => $apiData['receive_order_id'],
            ],
            // ---------- Mapped fields ----------
            [
                // Core
                'receive_order_date'                    => $apiData['receive_order_date'] ?? null,
                'receive_order_goods_amount'            => $apiData['receive_order_goods_amount'] ?? 0,
                'receive_order_delivery_fee_amount'     => $apiData['receive_order_delivery_fee_amount'] ?? 0,

                // Purchaser / Customer
                'receive_order_purchaser_id'            => $apiData['receive_order_purchaser_id'] ?? null,
                'receive_order_creator_name'            => $apiData['receive_order_creator_name'] ?? null,
                'receive_order_purchaser_tel'           => $apiData['receive_order_purchaser_tel'] ?? null,
                'receive_order_purchaser_mail_address'  => $apiData['receive_order_purchaser_mail_address'] ?? null,
                'receive_order_purchaser_address1'      => $apiData['receive_order_purchaser_address1'] ?? null,
                'receive_order_purchaser_address2'      => $apiData['receive_order_purchaser_address2'] ?? null,

                // Delivery & Status
                'receive_order_delivery_method_name'    => $apiData['receive_order_delivery_method_name'] ?? null,
                'receive_order_order_status_id'         => $apiData['receive_order_order_status_id'] ?? null,

                // Raw JSON for auditing / debugging
                'raw_response'                         => json_encode($apiData, JSON_UNESCAPED_UNICODE),
            ]
        );
    }

    /**
     * Bulk-sync a full API response into the database.
     *
     * @param  int   $shopId       The shop ID.
     * @param  array $apiResponse  The full response from fetchOrders() (contains 'data' key).
     * @return int   Number of orders synced.
     */
    public function syncFromApiResponse(int $shopId, array $apiResponse): int
    {
        $orders = $apiResponse['data'] ?? [];

        if (empty($orders)) {
            Log::info('NextEngineOrderRepository: No orders to sync', ['shop_id' => $shopId]);
            return 0;
        }

        $synced = 0;

        foreach ($orders as $orderData) {
            try {
                $this->updateOrCreateFromApi($shopId, $orderData);
                $synced++;
            } catch (\Exception $e) {
                Log::error('NextEngineOrderRepository: Failed to sync order', [
                    'shop_id'  => $shopId,
                    'order_id' => $orderData['receive_order_id'] ?? 'unknown',
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        Log::info("NextEngineOrderRepository: Synced {$synced}/" . count($orders) . " orders", [
            'shop_id' => $shopId,
        ]);

        return $synced;
    }
}
