<?php

namespace App\Providers;

use App\Connectors\PlatformConnectorFactory;
use App\Listeners\LogApiTrafficListener;
use App\Models\PlatformConnection;
use App\Models\PlatformOrder;
use App\Observers\StateLogObserver;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app['request']->server->set('HTTPS', true);

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
        URL::forceScheme('https');

        // Ignore SSL verification on local environment (Windows/XAMPP)
        if (app()->environment('local')) {
            Http::globalOptions(['verify' => false]);
        }

        Event::listen([
            ResponseReceived::class,
            ConnectionFailed::class,
        ], LogApiTrafficListener::class);

        // Register StateLog Observer for important models
        PlatformConnection::observe(StateLogObserver::class);
        PlatformOrder::observe(StateLogObserver::class);
    }
}
