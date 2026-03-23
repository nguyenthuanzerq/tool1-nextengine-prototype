<?php

namespace App\Services\ECPlatforms\NextEngine;

use App\Models\NextEngineConnection;
use App\Models\Shop;
use App\Services\ECPlatforms\Contracts\AuthenticatorInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NextEngineAuthenticator implements AuthenticatorInterface
{
    /** Next Engine OAuth2 endpoints */
    private const AUTH_URL  = 'https://base.next-engine.org/users/sign_in/';
    private const TOKEN_URL = 'https://api.next-engine.org/api_neauth/';

    private string $clientId;
    private string $clientSecret;
    private string $redirectUri;

    public function __construct()
    {
        $config = config('services.nextengine');

        $this->clientId     = $config['client_id']     ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';
        $this->redirectUri  = $config['redirect_uri']  ?? '';
    }

    // ----------------------------------------------------------------
    // AuthenticatorInterface
    // ----------------------------------------------------------------

    /**
     * Generate the Next Engine OAuth2 login URL for the given shop.
     */
    public function getAuthUrl(Shop $shop): string
    {
        $params = http_build_query([
            'client_id'    => $this->clientId,
            'client_secret'=> $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
        ]);

        return self::AUTH_URL . '?' . $params;
    }

    /**
     * Handle the OAuth callback: exchange the authorisation code for tokens
     * and persist them to `next_engine_connections`.
     */
    public function handleCallback(Shop $shop, array $requestData): void
    {
        $uid  = $requestData['uid']  ?? null;
        $state = $requestData['state'] ?? null;

        // -----------------------------------------------------------
        // 🔧  MOCK RESPONSE — uncomment the block below when testing
        //     locally without a real Next Engine sandbox.
        // -----------------------------------------------------------
        $responseData = [
            'access_token'  => 'mock_access_token_' . uniqid(),
            'refresh_token' => 'mock_refresh_token_' . uniqid(),
            'result'        => 'success',
            'company_ne_id' => 'NE000001',
            'uid'           => $uid,
            'state'         => $state,
        ];

        // -----------------------------------------------------------
        // TẠM TẮT GỌI API THẬT KHI ĐANG TEST LOCAL
        // -----------------------------------------------------------
        // try {
        //     $response = Http::asForm()->post(self::TOKEN_URL, [
        //         'uid'           => $uid,
        //         'state'         => $state,
        //         'client_id'     => $this->clientId,
        //         'client_secret' => $this->clientSecret,
        //     ]);

        //     $responseData = $response->json();

        //     if ($response->failed() || ($responseData['result'] ?? '') !== 'success') {
        //         Log::error('NextEngine token exchange failed', [
        //             'shop_id'  => $shop->id,
        //             'response' => $responseData,
        //         ]);
        //         throw new \RuntimeException(
        //             'Failed to exchange authorisation code: '
        //             . ($responseData['message'] ?? 'Unknown error')
        //         );
        //     }
        // } catch (\Illuminate\Http\Client\ConnectionException $e) {
        //     Log::warning('NextEngine API unreachable (local env?)', [
        //         'shop_id' => $shop->id,
        //         'error'   => $e->getMessage(),
        //     ]);
        //     throw $e;
        // }

        // Persist tokens
        NextEngineConnection::updateOrCreate(
            ['shop_id' => $shop->id],
            [
                'access_token'  => $responseData['access_token']  ?? null,
                'refresh_token' => $responseData['refresh_token'] ?? null,
                'expires_at'    => Carbon::now()->addHours(6),
                'status'        => 'connected',
            ]
        );
    }

    /**
     * Refresh the access token for the given shop using the stored refresh token.
     */
    public function refreshToken(Shop $shop): void
    {
        $connection = $this->getConnection($shop);

        // -----------------------------------------------------------
        // 🔧  MOCK RESPONSE — uncomment when testing locally
        // -----------------------------------------------------------
        $responseData = [
            'access_token'  => 'mock_refreshed_access_token_' . uniqid(),
            'refresh_token' => 'mock_refreshed_refresh_token_' . uniqid(),
            'result'        => 'success',
        ];

        // -----------------------------------------------------------
        // TẠM TẮT GỌI API THẬT KHI ĐANG TEST LOCAL
        // -----------------------------------------------------------
        // try {
        //     $response = Http::asForm()->post(self::TOKEN_URL, [
        //         'uid'           => $connection->access_token,
        //         'state'         => $connection->refresh_token,
        //         'client_id'     => $this->clientId,
        //         'client_secret' => $this->clientSecret,
        //     ]);

        //     $responseData = $response->json();

        //     if ($response->failed() || ($responseData['result'] ?? '') !== 'success') {
        //         Log::error('NextEngine token refresh failed', [
        //             'shop_id'  => $shop->id,
        //             'response' => $responseData,
        //         ]);
        //         throw new \RuntimeException(
        //             'Token refresh failed: ' . ($responseData['message'] ?? 'Unknown error')
        //         );
        //     }
        // } catch (\Illuminate\Http\Client\ConnectionException $e) {
        //     Log::warning('NextEngine API unreachable during refresh (local env?)', [
        //         'shop_id' => $shop->id,
        //         'error'   => $e->getMessage(),
        //     ]);
        //     throw $e;
        // }

        $connection->update([
            'access_token'  => $responseData['access_token']  ?? $connection->access_token,
            'refresh_token' => $responseData['refresh_token'] ?? $connection->refresh_token,
            'expires_at'    => Carbon::now()->addHours(6),
        ]);
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /**
     * Retrieve the NextEngineConnection for a shop, or fail.
     */
    public function getConnection(Shop $shop): NextEngineConnection
    {
        $connection = $shop->nextEngineConnection;

        if (! $connection) {
            throw new \RuntimeException("Shop [{$shop->id}] has no Next Engine connection.");
        }

        return $connection;
    }

    /**
     * Check whether the current access token has expired and needs refreshing.
     */
    public function tokenExpired(Shop $shop): bool
    {
        $connection = $this->getConnection($shop);

        return $connection->expires_at && Carbon::parse($connection->expires_at)->isPast();
    }
}
