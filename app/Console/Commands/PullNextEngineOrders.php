<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Services\PlatformSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PullNextEngineOrders extends Command
{
    protected $signature = 'app:pull-next-engine-orders';

    protected $description = 'Pull orders from connected NextEngine shops into the local order store.';

    public function handle(PlatformSyncService $syncService): int
    {
        $shops = Shop::whereHas('platform', fn ($query) => $query->where('key', 'nextengine'))->get();

        if ($shops->isEmpty()) {
            $this->info('No connected NextEngine shops found.');
            return self::SUCCESS;
        }

        $failed = false;
        foreach ($shops as $shop) {
            try {
                $count = $syncService->syncOrdersNow($shop);
                $this->info("Pulled {$count} orders for {$shop->shop_name}.");
            } catch (\Throwable $exception) {
                $failed = true;
                Log::error('Failed to pull NextEngine orders.', [
                    'shop_id' => $shop->id,
                    'error' => $exception->getMessage(),
                ]);
                $this->error("Failed to pull orders for {$shop->shop_name}: {$exception->getMessage()}");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
