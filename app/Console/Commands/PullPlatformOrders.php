<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Shop;
use App\Services\PlatformSyncService;
use Illuminate\Support\Facades\Log;

class PullPlatformOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pull-platform-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull new orders from connected marketplace platforms.';

    /**
     * Execute the console command.
     */
    public function handle(PlatformSyncService $syncService): int
    {
        $this->info('Starting to pull marketplace platform orders...');
        
        // Marketplace channels are order sources; NextEngine is the master target.
        $shops = Shop::where('auto_sync_enabled', true)
            ->whereHas('platform', function ($q) {
                $q->whereIn('key', ['yahoo', 'rakuten', 'shopify']);
            })->get();

        if ($shops->isEmpty()) {
            $this->info('No connected marketplace shops found.');
            return self::SUCCESS;
        }

        foreach ($shops as $shop) {
            $this->info("Pulling orders for shop: {$shop->shop_name} (ID: {$shop->id})");
            try {
                $count = $syncService->syncOrdersNow($shop);
                $this->info("Successfully pulled {$count} orders for shop {$shop->shop_name}");
            } catch (\Exception $e) {
                Log::error("Failed to pull orders for shop {$shop->id}: " . $e->getMessage());
                $this->error("Failed to pull orders for shop {$shop->shop_name}: " . $e->getMessage());
            }
        }
        
        $this->info('Finished pulling platform orders.');

        return self::SUCCESS;
    }
}
