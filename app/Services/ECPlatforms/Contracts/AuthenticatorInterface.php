<?php

namespace App\Services\ECPlatforms\Contracts;

use App\Models\Shop;

interface AuthenticatorInterface
{
    /**
     * Generate the OAuth/authorization URL for the given shop.
     */
    public function getAuthUrl(Shop $shop): string;

    /**
     * Handle the OAuth callback and persist tokens for the given shop.
     */
    public function handleCallback(Shop $shop, array $requestData): void;

    /**
     * Refresh the access token for the given shop.
     */
    public function refreshToken(Shop $shop): void;
}
