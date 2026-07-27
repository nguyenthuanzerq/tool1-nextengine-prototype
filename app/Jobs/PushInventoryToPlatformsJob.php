<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Shop;
use App\Models\PlatformConnection;
use App\Models\PlatformInventory;
use App\Connectors\PlatformConnectorFactory;
use Illuminate\Support\Facades\Log;

class PushInventoryToPlatformsJob implements ShouldQueue
{
    use Queueable;

    public $shopId;
    public $sku;
    public $quantity;

    /**
     * Create a new job instance.
     */
    public function __construct($shopId, $sku, $quantity)
    {
        $this->shopId = $shopId;
        $this->sku = $sku;
        $this->quantity = $quantity;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $shop = Shop::find($this->shopId);
        if (!$shop) {
            return;
        }

        // We want to push to Rakuten and Yahoo
        // Find their connections
        $connections = PlatformConnection::where('shop_id', $shop->id)
            ->whereHas('platform', function($q) {
                $q->whereIn('key', ['rakuten', 'yahoo']);
            })->get();

        foreach ($connections as $conn) {
            try {
                $connector = PlatformConnectorFactory::make($conn->platform->key);
                
                // Get the local inventory record for this platform to update status
                $inventory = PlatformInventory::where('platform_id', $conn->platform_id)
                    ->where('shop_id', $shop->id)
                    ->where('product_code', $this->sku)
                    ->first();

                if ($inventory) {
                    $inventory->update(['sync_status' => 'pending']);
                }

                $success = $connector->pushInventory($conn, $this->sku, $this->quantity);

                if ($inventory) {
                    $inventory->update([
                        'sync_status' => 'success',
                        'last_pushed_at' => now(),
                        'stock' => $this->quantity
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Failed to push inventory to platform {$conn->platform->key} for SKU {$this->sku}", [
                    'error' => $e->getMessage()
                ]);

                $inventory = PlatformInventory::where('platform_id', $conn->platform_id)
                    ->where('shop_id', $shop->id)
                    ->where('product_code', $this->sku)
                    ->first();

                if ($inventory) {
                    $inventory->update(['sync_status' => 'failed']);
                }
            }
        }
    }
}
