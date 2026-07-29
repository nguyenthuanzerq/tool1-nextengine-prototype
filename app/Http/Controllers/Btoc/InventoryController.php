<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\PlatformInventory;
use App\Models\Shop;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $shops     = Shop::with('platform')->orderBy('shop_name')->get();
        $platforms = Platform::orderBy('name')->get();

        $items = PlatformInventory::with(['shop', 'platform'])
            ->when($request->shop_id,     fn($q, $v) => $q->where('shop_id', $v))
            ->when($request->platform_id, fn($q, $v) => $q->where('platform_id', $v))
            ->when($request->product_code, fn($q, $v) => $q->where('product_code', 'like', "%{$v}%"))
            ->orderByDesc('last_synced_at')
            ->paginate(50)
            ->withQueryString();

        return view('btoc.inventory', compact('items', 'shops', 'platforms'));
    }

}
