<?php

namespace App\Services\NextEngine;

use Illuminate\Support\Facades\Http;

class NextEngineAuthService
{
    public function exchangeToken(string $uid, string $state): array
    {
        $response = Http::asForm()->post(
            config('services.next_engine.api_uri') . '/api_neauth',
            [
                'client_id'     => config('services.next_engine.client_id'),
                'client_secret' => config('services.next_engine.client_secret'),
                'uid'           => $uid,
                'state'         => $state
            ]
        );

        if (!$response->successful()) {
            throw new \Exception(
                'Next Engine token exchange failed: ' . $response->body()
            );
        }

        return $response->json();
    }

    public function fetchLoginUserInfo(
        string $accessToken,
        string $refreshToken
    ): array {

        $response = Http::asForm()->post(
            config('services.next_engine.api_uri') . '/api_v1_login_user/info',
            [
                'access_token'  => $accessToken,
                'refresh_token' => $refreshToken,
                'wait_flag'     => 1,
            ]
        );
        if (!$response->successful()) {
            throw new \Exception(
                'Next Engine login user info failed: ' . $response->body()
            );
        }

        $info = $response->json();

        if (($info['result'] ?? null) !== 'success') {
            throw new \Exception(
                'Next Engine login user info error: ' . json_encode($info)
            );
        }

        return $info;
    }
}
