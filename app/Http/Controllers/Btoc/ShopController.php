<?php

namespace App\Http\Controllers\Btoc;

use App\Connectors\NextEngineConnector;
use App\Connectors\YahooConnector;
use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\PlatformConnection;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::with(['platform', 'platformConnections', 'latestSyncHistory'])
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('btoc.shop.index', [
            'shops' => $shops,
        ]);
    }

    public function show($id)
    {
        $shop = Shop::with(['platform', 'platformConnections'])->findOrFail($id);

        $connection = $shop->platform_id
            ? PlatformConnection::where(['platform_id' => $shop->platform_id, 'shop_id' => $shop->id])->first()
            : null;

        $histories = \App\Models\SyncHistory::where('shop_id', $shop->id)
            ->latest('started_at')
            ->limit(20)
            ->get();

        $platforms = Platform::orderBy('name')->get();

        return view('btoc.shop.detail', [
            'shop'       => $shop,
            'connection' => $connection,
            'histories'  => $histories,
            'platforms'  => $platforms,
        ]);
    }

    public function create()
    {
        return view('btoc.shop.save', [
            'isCreate'   => true,
            'shop'       => new Shop,
            'platforms'  => Platform::orderBy('name')->get(),
            'connection' => null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_code'   => 'required|string|max:255|unique:shops,shop_code',
            'shop_name'   => 'required|string|max:255',
            'platform_id' => 'required|exists:platforms,id',
        ], [
            'shop_code.required'   => '店舗コードを空白のままにすることはできません。',
            'shop_code.unique'     => 'このショップコードは既に存在します。',
            'shop_name.required'   => 'ショップ名は空欄にできません。',
            'platform_id.required' => 'Vui lòng chọn platform.',
            'platform_id.exists'   => '無効なプラットフォームです。',
        ]);

        Shop::create($validated);

        return redirect()->route('btoc.shop.index')->with('success', 'ショップを追加しました。');
    }

    public function edit($id)
    {
        $shop = Shop::findOrFail($id);

        $connection = $shop->platform_id
            ? PlatformConnection::where(['platform_id' => $shop->platform_id, 'shop_id' => $shop->id])->first()
            : null;

        return view('btoc.shop.save', [
            'isCreate'   => false,
            'shop'       => $shop,
            'platforms'  => Platform::orderBy('name')->get(),
            'connection' => $connection,
        ]);
    }

    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $validated = $request->validate([
            'shop_code'   => 'required|string|max:255|unique:shops,shop_code,' . $shop->id,
            'shop_name'   => 'required|string|max:255',
            'platform_id' => 'required|exists:platforms,id',
        ]);

        $shop->update($validated);

        return redirect()->route('btoc.shop.index')->with('success', 'ショップ情報を更新しました。');
    }

    public function destroy($id)
    {
        $shop = Shop::findOrFail($id);
        $shop->delete();

        return redirect()->route('btoc.shop.index')->with('success', 'ショップを削除しました。');
    }

    public function connect(Request $request, NextEngineConnector $connector)
    {
        $shop = Shop::findOrFail($request->query('id'));

        // Store shop_id in session with a nonce so the callback can verify it
        // without relying on a query-string parameter that could be tampered with.
        $nonce = bin2hex(random_bytes(16));
        session([
            'ne_oauth_shop_id' => $shop->id,
            'ne_oauth_nonce'   => $nonce,
        ]);

        return redirect($connector->getAuthUrl($shop));
    }

    public function callback(Request $request, NextEngineConnector $connector)
    {
        // Validate session — shop_id must come from session, not from the URL
        $shopId = session('ne_oauth_shop_id');
        $nonce  = session('ne_oauth_nonce');

        if (! $shopId || ! $nonce) {
            abort(403, 'OAuth session expired or invalid. Please start the connection again.');
        }

        // Consume the nonce so the callback cannot be replayed
        session()->forget(['ne_oauth_shop_id', 'ne_oauth_nonce']);

        $shop = Shop::findOrFail($shopId);

        // Delegate token exchange to NextEngineConnector
        $connection = $connector->handleCallback($request, $shop);

        Log::info('NextEngine callback via connector', [
            'shop_id'   => $shop->id,
            'has_token' => (bool) $connection->access_token,
        ]);

        return redirect()->route('btoc.shop.edit', ['id' => $shop->id])
            ->with('success', '✓ NextEngine connected via connector.');
    }

    /**
     * Store NextEngine client credentials for a shop.
     */
    public function storeNextEngineConnection(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $rules = [
            'client_id'     => 'required|string|max:255',
            'client_secret' => 'required|string|max:255',
        ];

        // If the platform is Yahoo, seller_id is also required
        $platform = $shop->platform;
        if ($platform && $platform->key === 'yahoo') {
            $rules['seller_id'] = 'required|string|max:255';
        }

        $validated = $request->validate($rules);

        $platform = $platform ?: Platform::where('key', 'nextengine')->firstOrFail();

        $conn = PlatformConnection::firstOrNew([
            'platform_id' => $platform->id,
            'shop_id'     => $shop->id,
        ]);

        $credentialsChanged = ($validated['client_id'] !== $conn->client_id)
            || ($validated['client_secret'] !== $conn->client_secret)
            || (isset($validated['seller_id']) && $validated['seller_id'] !== $conn->seller_id);

        $conn->client_id     = $validated['client_id'];
        $conn->client_secret = $validated['client_secret'];
        if (isset($validated['seller_id'])) {
            $conn->seller_id = $validated['seller_id'];
        }

        if ($credentialsChanged) {
            $conn->access_token  = null;
            $conn->refresh_token = null;
            $conn->token_expires_at = null;
        }

        $conn->save();

        // Ensure shop.platform_id is set
        if (! $shop->platform_id) {
            $shop->platform_id = $platform->id;
            $shop->save();
        }

        return redirect()->route('btoc.shop.edit', ['id' => $shop->id])
            ->with('success', '✓ Credentials đã lưu vào platform_connections.');
    }

    public function connectYahoo(Request $request, YahooConnector $connector)
    {
        $shop = Shop::findOrFail($request->query('id'));

        $nonce = bin2hex(random_bytes(16));
        session([
            'yahoo_oauth_shop_id' => $shop->id,
            'yahoo_oauth_nonce'   => $nonce,
        ]);

        return redirect($connector->getAuthUrl($shop));
    }

    public function callbackYahoo(Request $request, YahooConnector $connector)
    {
        $shopId = session('yahoo_oauth_shop_id');
        $nonce  = session('yahoo_oauth_nonce');

        if (! $shopId || ! $nonce) {
            abort(403, 'OAuth session expired or invalid. Please start the connection again.');
        }

        // Validate state
        if ($request->query('state') !== $nonce) {
            abort(403, 'Invalid state parameter. Possible CSRF attack.');
        }

        session()->forget(['yahoo_oauth_shop_id', 'yahoo_oauth_nonce']);

        $shop = Shop::findOrFail($shopId);

        $connection = $connector->handleCallback($request, $shop);

        Log::info('Yahoo Shopping callback via connector', [
            'shop_id'   => $shop->id,
            'has_token' => (bool) $connection->access_token,
        ]);

        return redirect()->route('btoc.shop.edit', ['id' => $shop->id])
            ->with('success', '✓ Yahoo Shopping connected via connector.');
    }

}
