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
     * INVENTORY LEGACY SERVICE METHOD: candidate for deletion with inventory sync flow.
     */
    // public function dispatchInventorySync(Shop $shop): SyncHistory
    // {
    //     $conn    = $this->resolveConnection($shop);
    //     $history = $this->createHistory($shop, $conn, 'inventory');

    //     PlatformSyncJob::dispatch($shop->id, $conn->id, 'inventory', $history->id);

    //     return $history;
    // }

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
     * INVENTORY LEGACY SERVICE METHOD: candidate for deletion with inventory sync flow.
     * Returns count of rows processed.
     */
    // public function syncInventoryNow(Shop $shop): int
    // {
    //     $conn      = $this->resolveConnection($shop);
    //     $connector = $this->factory->resolve($shop->platform->key);
    //     $history   = $this->createHistory($shop, $conn, 'inventory');

    //     try {
    //         if ($connector instanceof OAuthConnector) {
    //             $connector->refreshTokenIfNeeded($conn);
    //         }
    //         $rows  = $connector->fetchInventory($conn);
    //         $count = $this->persistInventory($rows, $shop, $conn, $connector);

    //         $history->update([
    //             'status'   => 'success',
    //             'ended_at' => now(),
    //             'meta'     => ['synced_count' => $count],
    //         ]);

    //         return $count;
    //     } catch (\Throwable $e) {
    //         $history->update([
    //             'status'        => 'failed',
    //             'ended_at'      => now(),
    //             'error_message' => $e->getMessage(),
    //         ]);
    //         throw $e;
    //     }
    // }

    /**
     * Persist raw inventory rows from connector into platform_inventories.
     * Uses connector->normalizeInventory() to map platform fields → standard fields.
     * Returns the number of rows upserted.
     */
    // INVENTORY LEGACY SERVICE METHOD: candidate for deletion with inventory sync flow
    // public function persistInventory(iterable $rows, Shop $shop, PlatformConnection $conn, PlatformConnector $connector): int
    // {
    //     $count = 0;
    //     foreach ($rows as $row) {
    //         $normalized = $connector->normalizeInventory($row);

    //         \App\Models\PlatformInventory::updateOrCreate(
    //             [
    //                 'platform_id'  => $conn->platform_id,
    //                 'shop_id'      => $shop->id,
    //                 'product_code' => $normalized['product_code'],
    //             ],
    //             array_merge($normalized, [
    //                 'last_synced_at' => now(),
    //                 'meta'           => $row,
    //             ])
    //         );
    //         $count++;
    //     }
    //     return $count;
    // }

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
}
