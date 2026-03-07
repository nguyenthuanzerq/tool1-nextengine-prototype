<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Services\NextEngine\NextEngineAuthService;
use App\Services\NextEngine\NextEngineClientFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    public function __construct(
        private NextEngineAuthService $authService,
        private NextEngineClientFactory $clientFactory
    ) {}

    // =====================================================
    // SHOP LIST / CRUD
    // =====================================================

    public function shops()
    {
        $shops = Shop::all();
        return view('btoc.shops', compact('shops'));
    }

    /**
     * Xem chi tiết shop (read-only).
     * URL: GET /btoc/shops/{id}
     */
    public function show($id)
    {
        $shop = Shop::findOrFail($id);

        return view('btoc.shop_detail', [
            'shop'   => $shop,
            'mode'   => 'show',
            'action' => route('btoc.shop.update', $id),
        ]);
    }

    public function shopCreate()
    {
        return view('btoc.shop_detail', [
            'shop'   => new Shop(),
            'mode'   => 'create',
            'action' => route('btoc.shop.store'),
        ]);
    }

    public function shopEdit($id)
    {
        $shop = Shop::findOrFail($id);

        return view('btoc.shop_detail', [
            'shop'   => $shop,
            'mode'   => 'edit',
            'action' => route('btoc.shop.update', $id),
        ]);
    }

    public function shopStore(Request $request)
    {
        $validated = $request->validate([
            'shop_code'     => 'required|string|max:50|unique:shops,shop_code',
            'shop_name'     => 'required|string|max:255',
            'client_id'     => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
        ]);

        Shop::create($validated);

        return redirect()
            ->route('btoc.shops')
            ->with('success', 'ショップを登録しました。 (Shop created successfully.)');
    }

    public function shopUpdate(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $validated = $request->validate([
            'shop_code'     => 'required|string|max:50|unique:shops,shop_code,' . $shop->id,
            'shop_name'     => 'required|string|max:255',
            'client_id'     => 'nullable|string|max:255',
            'client_secret' => 'nullable|string|max:255',
        ]);

        $shop->update($validated);

        return redirect()
            ->route('btoc.shops')
            ->with('success', 'ショップを更新しました。 (Shop updated successfully.)');
    }

    // =====================================================
    // NEXTENGINE OAUTH
    // =====================================================

    /**
     * Redirect shop sang NextEngine để xác thực lại.
     * URL: GET /btoc/shop/{id}/re-authorize
     */
    public function reAuthorize($id)
    {
        $shop = Shop::findOrFail($id);

        $clientId = $shop->client_id ?? config('services.next_engine.client_id');

        $authUrl = rtrim(config('services.next_engine.base_uri'), '/')
            . '/api_neauth'
            . '?client_id=' . urlencode($clientId)
            . '&state=' . $shop->id;

        Log::info('NE_REAUTHORIZE_REDIRECT', [
            'shop_id'  => $shop->id,
            'auth_url' => $authUrl,
        ]);

        return redirect()->away($authUrl);
    }

    /**
     * NextEngine gọi lại sau khi user xác thực.
     * URL: GET /btoc/nextengine/callback?uid=...&state={shop_id}
     */
    public function callback(Request $request)
    {
        $uid    = $request->query('uid');
        $state  = $request->query('state');
        $shopId = (int) $state;

        if (!$uid || !$shopId) {
            return redirect()
                ->route('btoc.shops')
                ->with('error', 'コールバックパラメータが不正です。(Invalid callback parameters.)');
        }

        try {
            $tokenData = $this->authService->exchangeToken($uid, $state);

            $accessToken  = $tokenData['access_token']  ?? null;
            $refreshToken = $tokenData['refresh_token'] ?? null;

            if (!$accessToken) {
                throw new \Exception('No access_token in response: ' . json_encode($tokenData));
            }

            $shop = Shop::findOrFail($shopId);
            $shop->update([
                'access_token'     => $accessToken,
                'refresh_token'    => $refreshToken,
                'token_expires_at' => now()->addHours(1),
            ]);

            Log::info('NE_CALLBACK_SUCCESS', ['shop_id' => $shopId]);

            return redirect()
                ->route('btoc.shops')
                ->with('success', 'NextEngine 認証が完了しました。(Authorization successful.)');

        } catch (\Exception $e) {
            Log::error('NE_CALLBACK_ERROR', [
                'shop_id' => $shopId,
                'error'   => $e->getMessage(),
            ]);

            return redirect()
                ->route('btoc.shops')
                ->with('error', 'NextEngine 認証に失敗しました: ' . $e->getMessage());
        }
    }

    // =====================================================
    // CONNECTION MANAGEMENT
    // =====================================================

    /**
     * Test xem token của shop còn hoạt động không.
     * URL: POST /btoc/shop/{id}/test-connection
     */
    public function testConnection($id)
    {
        $shop = Shop::findOrFail($id);

        if (!$shop->access_token) {
            return response()->json([
                'status'  => 'error',
                'message' => 'アクセストークンが未設定です。(Access token not set.)',
            ], 422);
        }

        try {
            $client   = $this->clientFactory->make($shop);
            $response = $client->post('/api_v1_login_user/info', [
                'access_token'  => $shop->access_token,
                'refresh_token' => $shop->refresh_token,
                'wait_flag'     => 1,
            ]);

            $isSuccess = ($response['result'] ?? null) === 'success';

            Log::info('NE_TEST_CONNECTION', [
                'shop_id' => $shop->id,
                'result'  => $response['result'] ?? 'unknown',
            ]);

            return response()->json([
                'status'   => $isSuccess ? 'success' : 'error',
                'message'  => $isSuccess
                    ? '接続OK (Connection is valid.)'
                    : 'トークンが無効または期限切れです。(Token invalid or expired.)',
                'response' => $response,
            ]);

        } catch (\Exception $e) {
            Log::error('NE_TEST_CONNECTION_ERROR', [
                'shop_id' => $shop->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => '接続エラー: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dùng refresh_token để lấy access_token mới.
     * URL: POST /btoc/shop/{id}/refresh-token
     */
    public function refreshToken($id)
    {
        $shop = Shop::findOrFail($id);

        if (!$shop->refresh_token) {
            return response()->json([
                'status'  => 'error',
                'message' => 'リフレッシュトークンが未設定です。再認証してください。(Refresh token not set. Please re-authorize.)',
            ], 422);
        }

        try {
            $response = Http::asForm()->post(
                rtrim(config('services.next_engine.api_uri'), '/') . '/api_neauth',
                [
                    'client_id'     => $shop->client_id ?? config('services.next_engine.client_id'),
                    'client_secret' => $shop->client_secret ?? config('services.next_engine.client_secret'),
                    'refresh_token' => $shop->refresh_token,
                ]
            );

            $data            = $response->json() ?? [];
            $newAccessToken  = $data['access_token']  ?? null;
            $newRefreshToken = $data['refresh_token']  ?? $shop->refresh_token;

            if (!$newAccessToken) {
                throw new \Exception('Refresh returned no token: ' . json_encode($data));
            }

            $shop->update([
                'access_token'     => $newAccessToken,
                'refresh_token'    => $newRefreshToken,
                'token_expires_at' => now()->addHours(1),
            ]);

            Log::info('NE_TOKEN_REFRESHED', ['shop_id' => $shop->id]);

            return response()->json([
                'status'  => 'success',
                'message' => 'トークンを更新しました。(Token refreshed successfully.)',
            ]);

        } catch (\Exception $e) {
            Log::error('NE_REFRESH_TOKEN_ERROR', [
                'shop_id' => $shop->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'トークン更新に失敗しました: ' . $e->getMessage(),
            ], 500);
        }
    }
}
