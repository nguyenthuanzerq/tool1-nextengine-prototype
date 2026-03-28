<?php

namespace App\Contracts;

use App\Models\PlatformConnection;
use App\Models\Shop;
use Illuminate\Http\Request;

/** For OAuth2 platforms: NextEngine, Yahoo */
interface OAuthConnector extends PlatformConnector
{
    /** Build the redirect URL that sends the user to the platform login page. */
    public function getAuthUrl(Shop $shop): string;

    /** Exchange the OAuth callback params (uid/state or code) for tokens.
     *  Persists a PlatformConnection and returns it. */
    public function handleCallback(Request $request, Shop $shop): PlatformConnection;

    /** Refresh the access token if it is expired or about to expire. */
    public function refreshTokenIfNeeded(PlatformConnection $conn): void;
}
