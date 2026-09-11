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
                'meta'     => ['synced_count' => $count, 'platform' => $shop->platform->key],
            ]);

            return $count;
        } catch (\Throwable $e) {
            $history->update([
                'status'        => 'failed',
                'ended_at'      => now(),
                'error_message' => $this->summarizeError($e),
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

            if (empty($normalized['platform_order_id'])) {
                continue;
            }

            $existing = PlatformOrder::where([
                'platform_id'       => $conn->platform_id,
                'shop_id'           => $shop->id,
                'platform_order_id' => $normalized['platform_order_id'],
            ])->first();

            $attributes = array_merge($normalized, [
                'platform_id' => $conn->platform_id,
                'shop_id'     => $shop->id,
                'synced_at'   => now(),
                'raw_data'    => $row,
            ]);

            // Pulling from NextEngine must not requeue or overwrite local push state.
            if ($existing) {
                unset($attributes['sync_status'], $attributes['nextengine_order_id']);
                $order = tap($existing)->update($attributes);
            } else {
                $order = PlatformOrder::create(array_merge($attributes, [
                    // Marketplace orders are queued for the NextEngine master.
                    'sync_status' => $shop->platform->key === 'nextengine'
                        ? PlatformOrder::STATUS_SUCCESS
                        : PlatformOrder::STATUS_PENDING,
                ]));
            }

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

            // Each connector may use a different source order identifier.
            $platformOrderId = $normalized['orderNumber']
                ?? $raw['receive_order_id']
                ?? $raw['orderNumber']
                ?? $raw['OrderId']
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
     * Legacy inventory sync retained for the out-of-scope module.
     * It is not exposed by routes or scheduled jobs.
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
                'error_message' => $this->summarizeError($e),
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
    public function pushPendingOrdersToNextEngine(): int
    {
        $connector = $this->factory->resolve('nextengine');
        $processed = 0;

        $autoPushEnabled = Shop::where('auto_push_enabled', true)
            ->whereHas('platform', fn ($query) => $query->where('key', 'nextengine'))
            ->exists();

        if (! $autoPushEnabled) {
            return 0;
        }

        // Auto-push is controlled by the NextEngine master shop, while the
        // order remains owned by its originating marketplace shop.
        $pendingOrders = PlatformOrder::pending()
            ->whereNull('nextengine_order_id')
            ->whereHas('platform', fn ($query) => $query->whereIn('key', ['yahoo', 'rakuten', 'shopify']))
            ->with(['items', 'shop', 'platform'])
            ->get();

        foreach ($pendingOrders as $order) {
            $conn = PlatformConnection::whereHas('platform', fn ($query) => $query->where('key', 'nextengine'))
                ->where('shop_id', Shop::where('auto_push_enabled', true)
                    ->whereHas('platform', fn ($query) => $query->where('key', 'nextengine'))
                    ->value('id'))
                ->first();

            if (! $conn) {
                $this->markPushFailed($order, 'No NextEngine connection found for this shop.', $order->platform_id);
                $processed++;
                continue;
            }

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
                        $this->markPushFailed($order, $errMsg, $conn->platform_id);
                        
                        // Check for token or auth errors
                        $errMsgLower = strtolower($errMsg);
                        if (str_contains($errMsgLower, 'token') || str_contains($errMsgLower, 'unauthorized') || str_contains($errMsgLower, 'auth')) {
                            \Illuminate\Support\Facades\Log::emergency('NextEngine Token Expired/Invalid. Stopping sync process.');
                            break;
                        }
                    }
                    $processed++;
                } else {
                    $this->markPushFailed($order, 'NextEngine connector does not support order push.', $order->platform_id);
                    $processed++;
                }
            } catch(\Throwable $e) {
                $errMsg = $e->getMessage();
                $this->markPushFailed($order, $errMsg, $conn->platform_id);

                // Check for token or auth errors
                $errMsgLower = strtolower($errMsg);
                if (str_contains($errMsgLower, 'token') || str_contains($errMsgLower, 'unauthorized') || str_contains($errMsgLower, 'auth')) {
                    \Illuminate\Support\Facades\Log::emergency('NextEngine Token Expired/Invalid. Stopping sync process.');
                    break;
                }
                $processed++;
            }
        }

        return $processed;
    }

    private function markPushFailed(PlatformOrder $order, string $message, int $platformId): void
    {
        $order->update(['sync_status' => PlatformOrder::STATUS_FAILED]);
        SyncHistory::create([
            'shop_id'       => $order->shop_id,
            'platform_id'   => $platformId,
            'sync_code'     => 'PUSH_ORDER_' . now()->format('YmdHis') . '_' . Str::upper(Str::random(4)),
            'shop_name'     => $order->shop?->shop_name ?? 'Unknown',
            'sync_type'     => 'orders_push',
            'started_at'    => now(),
            'ended_at'      => now(),
            'status'        => 'failed',
            'error_message' => $this->truncateError($message),
            'meta'          => ['order_id' => $order->id],
        ]);
    }

    private function summarizeError(\Throwable $exception): string
    {
        return $this->truncateError($exception->getMessage());
    }

    private function truncateError(string $message): string
    {
        return mb_substr($message, 0, 255);
    }
}
