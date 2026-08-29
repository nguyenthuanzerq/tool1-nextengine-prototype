<?php

namespace App\Console\Commands;

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
    public function handle()
    {
        $this->info('Starting order sync to NextEngine...');
        app(\App\Services\PlatformSyncService::class)->pushPendingOrdersToNextEngine();
        $this->info('Sync completed!');
    }
}
