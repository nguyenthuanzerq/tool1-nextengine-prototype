<?php

namespace App\Connectors;

use App\Contracts\PlatformConnector;
use RuntimeException;

class PlatformConnectorFactory
{
    /** Map platform key → connector class */
    private array $map = [
        'nextengine' => NextEngineConnector::class,
        'yahoo'      => YahooConnector::class,
        'rakuten'    => RakutenConnector::class,
    ];

    /**
     * Resolve the connector for the given platform key.
     *
     * @throws RuntimeException if the platform key is unknown
     */
    public function resolve(string $platformKey): PlatformConnector
    {
        $class = $this->map[$platformKey] ?? null;

        if (! $class) {
            throw new RuntimeException("No connector registered for platform: {$platformKey}");
        }

        return app($class);
    }

    /** Register or override a connector at runtime (useful for testing). */
    public function register(string $platformKey, string $connectorClass): void
    {
        $this->map[$platformKey] = $connectorClass;
    }
}
