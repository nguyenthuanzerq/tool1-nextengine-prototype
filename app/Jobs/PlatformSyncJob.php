<?php

namespace App\Jobs;

use App\Connectors\PlatformConnectorFactory;
use App\Models\PlatformConnection;
use App\Models\Shop;
use App\Models\SyncHistory;
use App\Services\PlatformSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PlatformSyncJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /** Number of times the job may be attempted. */
    public int $tries = 3;

    /** Timeout in seconds. */
    public int $timeout = 300;

    public function __construct(
        private int    $shopId,
        private int    $connectionId,
        private string $syncType,   // 'orders' only in the active runtime scope
        private int    $historyId,
    ) {}

    public function handle(
        PlatformConnectorFactory $factory,
        PlatformSyncService      $service
    ): void {
        $shop    = Shop::with('platform')->findOrFail($this->shopId);
        $conn    = PlatformConnection::findOrFail($this->connectionId);
        $history = SyncHistory::findOrFail($this->historyId);

        $connector = $factory->resolve($shop->platform->key);

        try {
            if (! method_exists($connector, 'refreshTokenIfNeeded')) {
                throw new \LogicException('NextEngine connector does not support token refresh.');
            }
            $connector->refreshTokenIfNeeded($conn);

            if ($this->syncType === 'orders') {
                $rows  = $connector->fetchOrders($conn);
                $count = $service->persistOrders($rows, $shop, $conn, $connector);
                $history->update([
                    'status'   => 'success',
                    'ended_at' => now(),
                    'meta'     => ['synced_count' => $count],
                ]);
                Log::info("PlatformSyncJob [orders] done", ['shop' => $shop->id, 'count' => $count]);
            } else {
                throw new \InvalidArgumentException("Unknown sync_type: {$this->syncType}");
            }
        } catch (\Throwable $e) {
            $history->update([
                'status'        => 'failed',
                'ended_at'      => now(),
                'error_message' => mb_substr($e->getMessage(), 0, 65535),
            ]);
            Log::error("PlatformSyncJob failed", [
                'shop'      => $this->shopId,
                'sync_type' => $this->syncType,
                'error'     => $e->getMessage(),
            ]);
            throw $e; // re-throw so queue retries
        }
    }

    public function failed(\Throwable $e): void
    {
        // Mark history as failed after all retries exhausted
        SyncHistory::where('id', $this->historyId)
            ->where('status', 'running')
            ->update([
                'status'        => 'failed',
                'ended_at'      => now(),
                'error_message' => 'Job failed after retries: ' . $e->getMessage(),
            ]);
    }
}
