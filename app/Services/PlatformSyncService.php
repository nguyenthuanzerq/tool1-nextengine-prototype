<?php

namespace App\Services;

use App\Connectors\PlatformConnectorFactory;
use App\Contracts\OAuthConnector;
use App\Contracts\PlatformConnector;
use App\Jobs\PlatformSyncJob;
use App\Models\PlatformConnection;
use App\Models\PlatformOrder;
use App\Models\PlatformOrderItem;
use App\Models\Shop;
use App\Models\SyncHistory;
use Illuminate\Support\Str;

class PlatformSyncService
{
    public function __construct(
        private PlatformConnectorFactory $factory
    ) {}

    /**
     * Dispatch an order sync job for the given shop.
     * Creates a SyncHistory record (status=running) and returns it.
     */
    public function dispatchOrderSync(Shop $shop): SyncHistory
    {
        $conn    = $this->resolveConnection($shop);
        $history = $this->createHistory($shop, $conn, 'orders');

        PlatformSyncJob::dispatch($shop->id, $conn->id, 'orders', $history->id);

        return $history;
    }

    /**
     * Dispatch an inventory sync job for the given shop.
     */
    public function dispatchInventorySync(Shop $shop): SyncHistory
    {
        $conn    = $this->resolveConnection($shop);
        $history = $this->createHistory($shop, $conn, 'inventory');

        PlatformSyncJob::dispatch($shop->id, $conn->id, 'inventory', $history->id);

        return $history;
    }

    /**
     * Run order sync synchronously (used by ShopController for now).
     * Returns count of rows processed.
     */
    public function syncOrdersNow(Shop $shop): int
    {
        $conn      = $this->resolveConnection($shop);
        $connector = $this->factory->resolve($shop->platform->key);
        $history   = $this->createHistory($shop, $conn, 'orders');

        try {
            if ($connector instanceof OAuthConnector) {
                $connector->refreshTokenIfNeeded($conn);
            }
            $rows  = $connector->fetchOrders($conn);
            $count = $this->persistOrders($rows, $shop, $conn, $connector);

            $history->update([
                'status'   => 'success',
                'ended_at' => now(),
                'meta'     => ['synced_count' => $count],
            ]);

            return $count;
        } catch (\Throwable $e) {
            $history->update([
                'status'        => 'failed',
                'ended_at'      => now(),
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Persist raw order rows from connector into platform_orders.
     * Uses connector->normalizeOrder() to map platform fields → standard fields.
     * Returns the number of rows upserted.
     */
    public function persistOrders(iterable $rows, Shop $shop, PlatformConnection $conn, PlatformConnector $connector): int
    {
        $count        = 0;
        $savedOrders  = [];  // platform_order_id → PlatformOrder->id

        foreach ($rows as $row) {
            $normalized = $connector->normalizeOrder($row);

            $order = PlatformOrder::updateOrCreate(
                [
                    'platform_id'       => $conn->platform_id,
                    'platform_order_id' => $normalized['platform_order_id'],
                ],
                array_merge($normalized, [
                    'shop_id'   => $shop->id,
                    'synced_at' => now(),
                    'raw_data'  => $row,
                ])
            );

            $savedOrders[$normalized['platform_order_id']] = $order->id;
            $count++;
        }

        // Batch-fetch and persist line items for all saved orders
        if (! empty($savedOrders)) {
            $this->persistOrderItems(
                array_keys($savedOrders),
                $savedOrders,
                $conn,
                $connector
            );
        }

        return $count;
    }

    /**
     * Batch-fetch line items for the given platform order IDs and upsert into platform_order_items.
     *
     * @param string[] $platformOrderIds  Platform-side order IDs
     * @param array    $orderIdMap        platform_order_id → platform_orders.id (our PK)
     */
    private function persistOrderItems(array $platformOrderIds, array $orderIdMap, PlatformConnection $conn, PlatformConnector $connector): void
    {
        $rawItems = $connector->fetchOrderItems($conn, $platformOrderIds);

        foreach ($rawItems as $raw) {
            $normalized = $connector->normalizeOrderItem($raw);

            // Resolve our internal platform_orders.id from the platform order ID
            $platformOrderId = $raw['receive_order_id']  // NE
                ?? $raw['OrderId']                        // Yahoo
                ?? $raw['orderNumber']                    // Rakuten
                ?? null;

            $internalOrderId = $orderIdMap[$platformOrderId] ?? null;

            if (! $internalOrderId || empty($normalized['platform_item_id'])) {
                continue;
            }

            PlatformOrderItem::updateOrCreate(
                [
                    'platform_order_id' => $internalOrderId,
                    'platform_item_id'  => $normalized['platform_item_id'],
                ],
                array_merge($normalized, [
                    'meta' => $raw,
                ])
            );
        }
    }

    /**
     * Run inventory sync synchronously (used by SyncController).
     * Returns count of rows processed.
     */
    public function syncInventoryNow(Shop $shop): int
    {
        $conn      = $this->resolveConnection($shop);
        $connector = $this->factory->resolve($shop->platform->key);
        $history   = $this->createHistory($shop, $conn, 'inventory');

        try {
            if ($connector instanceof OAuthConnector) {
                $connector->refreshTokenIfNeeded($conn);
            }
            $rows  = $connector->fetchInventory($conn);
            $count = $this->persistInventory($rows, $shop, $conn, $connector);

            $history->update([
                'status'   => 'success',
                'ended_at' => now(),
                'meta'     => ['synced_count' => $count],
            ]);

            return $count;
        } catch (\Throwable $e) {
            $history->update([
                'status'        => 'failed',
                'ended_at'      => now(),
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Persist raw inventory rows from connector into platform_inventories.
     * Uses connector->normalizeInventory() to map platform fields → standard fields.
     * Returns the number of rows upserted.
     */
    public function persistInventory(iterable $rows, Shop $shop, PlatformConnection $conn, PlatformConnector $connector): int
    {
        $count = 0;
        foreach ($rows as $row) {
            $normalized = $connector->normalizeInventory($row);

            $existing = \App\Models\PlatformInventory::where([
                'platform_id'  => $conn->platform_id,
                'shop_id'      => $shop->id,
                'product_code' => $normalized['product_code'],
            ])->first();

            $oldStock = $existing ? $existing->stock : null;

            \App\Models\PlatformInventory::updateOrCreate(
                [
                    'platform_id'  => $conn->platform_id,
                    'shop_id'      => $shop->id,
                    'product_code' => $normalized['product_code'],
                ],
                array_merge($normalized, [
                    'last_synced_at' => now(),
                    'meta'           => $row,
                ])
            );

            // Push logic disabled for read-only dashboard
            // if ($conn->platform->key === 'nextengine' && $oldStock !== $normalized['stock']) {
            //     $this->pushInventoryUpdate($shop, $normalized['product_code'], $normalized['stock']);
            // }

            $count++;
        }
        return $count;
    }

    /**
     * Push a shipment tracking number to the platform immediately.
     */
    public function pushShipmentNow(Shop $shop, PlatformOrder $order, string $trackingNumber, array $extraData = []): void
    {
        $conn      = $this->resolveConnection($shop);
        $connector = $this->factory->resolve($shop->platform->key);
        
        $trackingData = array_merge([
            'tracking_number' => $trackingNumber,
            'shipped_at'      => now()->format('Y-m-d'),
        ], $extraData);

        if (method_exists($connector, 'updateShipment')) {
            $connector->updateShipment($conn, $order->platform_order_id, $trackingData);
        } else {
            throw new \RuntimeException("Connector does not support updateShipment.");
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function resolveConnection(Shop $shop): PlatformConnection
    {
        $conn = PlatformConnection::where([
            'platform_id' => $shop->platform_id,
            'shop_id'     => $shop->id,
        ])->first();

        if (! $conn) {
            throw new \RuntimeException(
                "No platform_connection found for shop #{$shop->id}. Please connect the platform first."
            );
        }

        return $conn;
    }

    private function createHistory(Shop $shop, PlatformConnection $conn, string $syncType): SyncHistory
    {
        return SyncHistory::create([
            'shop_id'     => $shop->id,
            'platform_id' => $conn->platform_id,
            'sync_code'   => strtoupper($syncType) . '_' . now()->format('YmdHis') . '_' . Str::upper(Str::random(4)),
            'shop_name'   => $shop->shop_name,
            'sync_type'   => $syncType,
            'started_at'  => now(),
            'status'      => 'running',
        ]);
    }
    public function pushPendingOrdersToNextEngine(): void
    {
        $conn = PlatformConnection::whereHas('platform', function ($q) {
            $q->where('key', 'nextengine');
        })->first();

        if (!$conn) {
            \Illuminate\Support\Facades\Log::warning('No NextEngine connection found for pushing orders.');
            return;
        }

        $connector = $this->factory->resolve('nextengine');

        $pendingOrders = PlatformOrder::where('sync_status', 'pending')->with(['items', 'shop'])->get();

        foreach ($pendingOrders as $order) {
            try {
                if (method_exists($connector, 'pushOrder')) {
                    $result = $connector->pushOrder($conn, $order);
                    
                    if ($result['status'] === 'success') {
                        $order->update([
                            'sync_status'           => 'success',
                            'nextengine_order_id'   => $result['nextengine_order_id'] ?? null,
                            'platform_order_status' => '50',
                        ]);
                    } else {
                        $errMsg = $result['message'] ?? 'Failed to push order';
                        $order->update(['sync_status' => 'failed']);
                        
                        SyncHistory::create([
                            'shop_id'       => $order->shop_id,
                            'platform_id'   => $conn->platform_id,
                            'sync_code'     => 'PUSH_ORDER_' . now()->format('YmdHis') . '_' . Str::upper(Str::random(4)),
                            'shop_name'     => $order->shop->shop_name ?? 'Unknown',
                            'sync_type'     => 'orders_push',
                            'started_at'    => now(),
                            'ended_at'      => now(),
                            'status'        => 'failed',
                            'error_message' => $errMsg,
                        ]);
                        
                        // Check for token or auth errors
                        $errMsgLower = strtolower($errMsg);
                        if (str_contains($errMsgLower, 'token') || str_contains($errMsgLower, 'unauthorized') || str_contains($errMsgLower, 'auth')) {
                            \Illuminate\Support\Facades\Log::emergency('NextEngine Token Expired/Invalid. Stopping sync process.');
                            break;
                        }
                    }
                }
            } catch(\Throwable $e) {
                $errMsg = $e->getMessage();
                $order->update(['sync_status' => 'failed']);
                
                SyncHistory::create([
                    'shop_id'       => $order->shop_id,
                    'platform_id'   => $conn->platform_id,
                    'sync_code'     => 'PUSH_ORDER_' . now()->format('YmdHis') . '_' . Str::upper(Str::random(4)),
                    'shop_name'     => $order->shop->shop_name ?? 'Unknown',
                    'sync_type'     => 'orders_push',
                    'started_at'    => now(),
                    'ended_at'      => now(),
                    'status'        => 'failed',
                    'error_message' => substr($errMsg, 0, 255),
                ]);

                // Check for token or auth errors
                $errMsgLower = strtolower($errMsg);
                if (str_contains($errMsgLower, 'token') || str_contains($errMsgLower, 'unauthorized') || str_contains($errMsgLower, 'auth')) {
                    \Illuminate\Support\Facades\Log::emergency('NextEngine Token Expired/Invalid. Stopping sync process.');
                    break;
                }
            }
        }
    }
}
