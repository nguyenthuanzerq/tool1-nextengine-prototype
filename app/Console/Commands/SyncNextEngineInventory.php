<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PlatformConnection;
use App\Connectors\NextEngineConnector;
use App\Services\PlatformSyncService;

class SyncNextEngineInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:nextengine-inventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch recent inventory changes from NextEngine and push to other platforms';

    /**
     * Execute the console command.
     */
    public function handle(PlatformSyncService $syncService)
    {
        $this->info('Starting NextEngine recent inventory sync...');
        $connector = new NextEngineConnector();
        
        $connections = PlatformConnection::whereHas('platform', function($q) {
            $q->where('key', 'nextengine');
        })->get();

        foreach ($connections as $conn) {
            try {
                $this->info("Fetching changes for Shop {$conn->shop_id}");
                $rows = $connector->fetchRecentInventoryChanges($conn);
                if (!empty($rows)) {
                    $count = $syncService->persistInventory($rows, $conn->shop, $conn, $connector);
                    $this->info("Processed {$count} inventory records for Shop {$conn->shop_id}");
                } else {
                    $this->info("No recent inventory changes found for Shop {$conn->shop_id}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to sync for Shop {$conn->shop_id}: " . $e->getMessage());
            }
        }
        
        $this->info('NextEngine inventory sync completed.');
    }
}
