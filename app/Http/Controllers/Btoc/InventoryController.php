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

    public function forcePush($id)
    {
        $inventory = PlatformInventory::findOrFail($id);
        
        \App\Jobs\PushInventoryToPlatformsJob::dispatch(
            $inventory->shop_id, 
            $inventory->product_code, 
            $inventory->stock
        );

        return back()->with('success', 'Force push triggered for SKU: ' . $inventory->product_code);
    }

    public function syncMaster(Request $request, $shopId)
    {
        $shop = Shop::findOrFail($shopId);
        $sku = $request->input('sku');

        if ($sku) {
            // Because NextEngine API doesn't allow fetching single SKU easily in current implementation,
            // we will just run the SyncNextEngineInventory command logic or dispatch full sync.
            // For prototype, dispatching full inventory sync for the shop
            $syncService = app(\App\Services\PlatformSyncService::class);
            $syncService->dispatchInventorySync($shop);
            return back()->with('success', 'Triggered Master Sync for Shop. The SKU will be updated shortly.');
        }

        return back()->with('error', 'SKU is required.');
    }
}
