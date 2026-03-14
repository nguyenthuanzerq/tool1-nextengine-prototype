<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\NextEngine\NextEngineAuthService;

class ShopController extends Controller
{
    public function index()
    {
        $shops = Shop::orderBy('id', 'desc')->paginate(10);
        
        return view('btoc.shop.index', [
            'shops' => $shops
        ]);
    }

    public function show($id)
    {
        $shop = Shop::findOrFail($id);
        
        return view('btoc.shop.detail', [
            'shop' => $shop
        ]);
    }

    public function create()
    {
        $isCreate = true;
        $shop = new Shop(); 
        
        return view('btoc.shop.save', [
            'isCreate' => $isCreate,
            'shop' => $shop
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_code'     => 'required|string|max:255|unique:shops,shop_code',
            'shop_name'     => 'required|string|max:255',

        ], [
            'shop_code.required' => '店舗コードを空白のままにすることはできません。',
            'shop_code.unique'   => 'このショップコードは既に存在します。',
            'shop_name.required' => 'ショップ名は空欄にできません。',
        ]);

        Shop::create($validated);

        return redirect()->route('btoc.shop.index')->with('success', 'Đã thêm shop mới thành công.');
    }

    public function edit($id)
    {
        $isCreate = false;
        $shop = Shop::findOrFail($id); 
        
        return view('btoc.shop.save', [
            'isCreate' => $isCreate,
            'shop' => $shop
        ]);
    }

    public function update(Request $request, $id)
    {
        $shop = Shop::findOrFail($id);

        $validated = $request->validate([
            'shop_code'     => 'required|string|max:255|unique:shops,shop_code,' . $shop->id,
            'shop_name'     => 'required|string|max:255',
        ]);

        $shop->update($validated);

        return redirect()->route('btoc.shop.index')->with('success', 'Đã cập nhật thông tin shop thành công.');
    }

    public function destroy($id)
    {
        $shop = Shop::findOrFail($id);
        $shop->delete();

        return redirect()->route('btoc.shop.index')->with('success', 'Đã xóa shop thành công.');
    }

    public function callback(Request $request, NextEngineAuthService $authService)
    {
        $shopId = $request->query('shop_id');
        $uid = $request->query('uid');
        $state = $request->query('state');
        $shop = Shop::findOrFail($shopId);

        $response = Http::asForm()->post(
            config('services.next_engine.api_uri') . '/api_neauth',
            [
                'client_id'     => $shop->client_id,
                'client_secret' => $shop->client_secret,
                'uid'           => $uid,
                'state'         => $state
            ]
        );

        $shop->access_token = $response->json()['access_token'] ?? null;
        $shop->refresh_token = $response->json()['refresh_token'] ?? null;
        $shop->save();

        return redirect()->route("btoc.shop.edit", ['id' => $shop->id]);
    }

    public function connect(Request $request)
    {
        $shop = Shop::findOrFail($request->id);
        $redirectUri = config('services.next_engine.redirect_uri') . '?shop_id=' . $shop->id;
        $query = http_build_query([
            'client_id'    => $shop->client_id,
            'redirect_uri' => $redirectUri,
        ]);
        $baseUri = config('services.next_engine.base_uri');
        return redirect("$baseUri/users/sign_in?{$query}");
    }
}