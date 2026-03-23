<?php

namespace App\Services\ECPlatforms;

use App\Services\ECPlatforms\Contracts\ApiClientInterface;
use App\Services\ECPlatforms\Contracts\AuthenticatorInterface;
use App\Services\ECPlatforms\NextEngine\NextEngineApiClient;
use App\Services\ECPlatforms\NextEngine\NextEngineAuthenticator;
use InvalidArgumentException;

class PlatformFactory
{
    /**
     * Create an Authenticator instance for the given platform.
     *
     * @throws InvalidArgumentException
     */
    public function makeAuthenticator(string $platformCode): AuthenticatorInterface
    {
        return match ($platformCode) {
            'nextengine' => app(NextEngineAuthenticator::class),
            default => throw new InvalidArgumentException("Unsupported platform: {$platformCode}"),
        };
    }

    /**
     * Create an ApiClient instance for the given platform.
     *
     * @throws InvalidArgumentException
     */
    public function makeApiClient(string $platformCode): ApiClientInterface
    {
        return match ($platformCode) {
            'nextengine' => app(NextEngineApiClient::class),
            default => throw new InvalidArgumentException("Unsupported platform: {$platformCode}"),
        };
    }
}
