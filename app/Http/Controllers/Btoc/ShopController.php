<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shop;

class ShopController extends Controller
{
    public function shops()
{
     $shops = Shop::all();   // Lấy toàn bộ shop từ DB

    return view('btoc.shops', compact('shops'));
}

public function shopCreate()
{
    return view('btoc.shop_detail', [
        'shop'   => new \App\Models\Shop(),   // <- để không undefined
        'mode'   => 'create',
        'action' => route('btoc.shop.store'),
    ]);
}

public function shopEdit($id)
{
    $shop = \App\Models\Shop::findOrFail($id);

    return view('btoc.shop_detail', [
        'shop'   => $shop,
        'mode'   => 'edit',
        'action' => route('btoc.shop.update', $id),
    ]);
}

public function shopStore(Request $request)
{
    // 1. Validate backend (BẮT BUỘC)
    $validated = $request->validate([
        'shop_code'      => 'required|string|max:50|unique:shops,shop_code',
        'shop_name'      => 'required|string|max:255',
        'client_id'      => 'nullable|string|max:255',
        'client_secret'  => 'nullable|string|max:255',
        'login_id'       => 'nullable|string|max:255',
        'login_password' => 'nullable|string|max:255',
    ]);

    // 2. Create using validated data only
    Shop::create($validated);

    // 3. Redirect with success message
    return redirect()
        ->route('btoc.shops')
        ->with('success', 'Shop created successfully.');
}

public function shopUpdate(Request $request, $id)
{
    $shop = Shop::findOrFail($id);

    // 1. Validate backend
    $validated = $request->validate([
        'shop_code'      => 'required|string|max:50|unique:shops,shop_code,' . $shop->id,
        'shop_name'      => 'required|string|max:255',
        'client_id'      => 'nullable|string|max:255',
        'client_secret'  => 'nullable|string|max:255',
        'login_id'       => 'nullable|string|max:255',
        'login_password' => 'nullable|string|max:255',
    ]);

    // 2. Update using validated data only
    $shop->update($validated);

    // 3. Redirect
    return redirect()
        ->route('btoc.shops')
        ->with('success', 'Shop updated successfully.');
}
}
