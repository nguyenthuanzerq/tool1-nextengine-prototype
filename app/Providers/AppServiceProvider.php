<?php

namespace App\Providers;

use App\Connectors\PlatformConnectorFactory;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // $this->app['request']->server->set('HTTPS', true);

        // Register PlatformConnectorFactory as a singleton so all controllers
        // share the same instance (and any runtime overrides are preserved).
        $this->app->singleton(PlatformConnectorFactory::class, function () {
            return new PlatformConnectorFactory();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();
        // URL::forceScheme('https');
    }
}
