<?php

namespace App\Console\Commands;

use App\Models\Shop;
use Illuminate\Console\Command;

class SyncOrdersToNextEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-orders-to-next-engine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push pending orders to NextEngine automatically.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! Shop::where('auto_push_enabled', true)
            ->whereHas('platform', fn ($query) => $query->where('key', 'nextengine'))
            ->exists()) {
            $this->info('Auto-push is disabled for all NextEngine shops.');
            return self::SUCCESS;
        }

        $this->info('Starting order sync to NextEngine...');
        $processed = app(\App\Services\PlatformSyncService::class)->pushPendingOrdersToNextEngine();
        $this->info("Processed {$processed} order(s).");

        return self::SUCCESS;
    }
}
