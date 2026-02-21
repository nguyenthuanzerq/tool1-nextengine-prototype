<?php

namespace App\Services\NextEngine;

use Exception;
use Illuminate\Support\Facades\Http;

class NextEngineClient
{
    private array $config;

    public function __construct(array $config = [])
    {
        // Ưu tiên config từ shop, fallback env để không phá Phase 4
        $this->config = $config;
    }

    private function baseUri(): string
    {
        // domain riêng theo shop (nếu có), fallback env
        $domain = $this->config['domain'] ?? env('NEXT_ENGINE_DOMAIN');

        // nếu anh không dùng domain shop, có thể dùng NEXT_ENGINE_API_URI
        $apiUri = env('NEXT_ENGINE_API_URI'); // ví dụ: https://api.next-engine.org
        if ($apiUri) return rtrim($apiUri, '/');

        if (!$domain) {
            throw new Exception('NextEngine domain/api uri is missing.');
        }

        return rtrim($domain, '/');
    }

    private function accessToken(): ?string
    {
        return $this->config['access_token'] ?? env('NEXT_ENGINE_ACCESS_TOKEN');
    }

    private function refreshToken(): ?string
    {
        return $this->config['refresh_token'] ?? env('NEXT_ENGINE_REFRESH_TOKEN');
    }

    private function clientId(): ?string
    {
        return $this->config['client_id'] ?? env('NEXT_ENGINE_CLIENT_ID');
    }

    private function clientSecret(): ?string
    {
        return $this->config['client_secret'] ?? env('NEXT_ENGINE_CLIENT_SECRET');
    }


    /**
     * POST NextEngine API (form params)
     */
    public function post(string $path, array $params): array
    {
        $url = $this->baseUri() . '/' . ltrim($path, '/');

        $resp = Http::asForm()
            ->timeout(30)
            ->post($url, $params);

        if (!$resp->ok()) {
            throw new Exception("NextEngine API failed: {$resp->status()} - " . $resp->body());
        }

        $json = $resp->json();

        if (!is_array($json)) {
            throw new Exception("NextEngine API invalid JSON: " . $resp->body());
        }

        return $json;
    }

    /**
     * Upload receive order (CSV)
     * Endpoint theo manual Phase4: /api_v1_receiveorder_base/upload
     */
    public function uploadSalesOrder(string $data): array
{
    $connection = \App\Models\NextEngineConnection::latest('access_token_end_date')->first();

    if (!$connection) {
        return ['error' => 'NextEngineConnection not found'];
    }

    $response = Http::asForm()->post(
        config('services.next_engine.api_uri') . '/api_v1_receiveorder_base/upload',
        [
            'access_token'  => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'wait_flag'     => 1,
            'receive_order_upload_pattern_id' => 2,
            'data_type_1'   => 'csv',
            'data_1'        => $data,
        ]
    );

    return $response->json() ?? [];
}



    /**
     * Queue status check
     * Endpoint: /api_v1_system_que/search
     */
    public function uploadQueueSearch(string $que_id): array
{
    $connection = \App\Models\NextEngineConnection::latest('access_token_end_date')->first();

    if (!$connection) {
        return ['error' => 'NextEngineConnection not found'];
    }

    $response = Http::asForm()->post(
        config('services.next_engine.api_uri') . '/api_v1_system_que/search',
        [
            'access_token'  => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'wait_flag'     => 1,
            'fields' => 'que_id,que_method_name,que_shop_id,que_upload_name,que_file_name,que_message,que_creation_date,que_creator_name,que_status_id',
            'que_id-eq' => $que_id,
        ]
    );

    return $response->json() ?? [];
}

    /**
     * Order slip search (after queue success)
     * Endpoint: /api_v1_receiveorder_base/search
     */
   public function orderSlipSearch(int $receive_order_shop_cut_form_id): array
{
    $connection = \App\Models\NextEngineConnection::latest('access_token_end_date')->first();

    if (!$connection) {
        return ['error' => 'NextEngineConnection not found'];
    }

    $response = Http::asForm()->post(
        config('services.next_engine.api_uri') . '/api_v1_receiveorder_base/search',
        [
            'access_token'  => $connection->access_token,
            'refresh_token' => $connection->refresh_token,
            'wait_flag'     => 1,
            'fields' => 'receive_order_id,receive_order_shop_id,receive_order_shop_cut_form_id,receive_order_import_date,receive_order_order_status_id,receive_order_total_amount',
            'receive_order_shop_cut_form_id-eq' => $receive_order_shop_cut_form_id,
        ]
    );

    return $response->json() ?? [];
}

    /**
     * (Khung) refresh token - sẽ nối với NextEngineAuthService sau
     * Hiện tại để placeholder, không dùng vẫn OK.
     */
    public function refreshTokenIfNeeded(): void
    {
        // TODO: Wiring với NextEngineAuthService khi anh muốn.
        // Giữ trống để không ảnh hưởng Phase 4 hiện tại.
    }
}
