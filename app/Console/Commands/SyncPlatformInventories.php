<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlatformConnection;
use App\Connectors\PlatformConnectorFactory;
use App\Services\PlatformSyncService;

class SyncPlatformInventories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:platform-inventories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch inventory from Rakuten and Yahoo for Read-only Dashboard';

    /**
     * Execute the console command.
     */
    public function handle(PlatformSyncService $syncService, PlatformConnectorFactory $factory)
    {
        $this->info('Starting platform inventory sync (Rakuten/Yahoo)...');
        
        $connections = PlatformConnection::whereHas('platform', function($q) {
            $q->whereIn('key', ['rakuten', 'yahoo']);
        })->get();

        foreach ($connections as $conn) {
            try {
                $this->info("Fetching inventory for Shop {$conn->shop_id} ({$conn->platform->key})");
                $connector = $factory->resolve($conn->platform->key);
                
                $rows = $connector->fetchInventory($conn);
                if (!empty($rows)) {
                    $count = $syncService->persistInventory($rows, $conn->shop, $conn, $connector);
                    $this->info("Processed {$count} inventory records for Shop {$conn->shop_id}");
                } else {
                    $this->info("No inventory data found for Shop {$conn->shop_id}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to sync for Shop {$conn->shop_id}: " . $e->getMessage());
            }
        }
        
        $this->info('Platform inventory sync completed.');
    }
}
