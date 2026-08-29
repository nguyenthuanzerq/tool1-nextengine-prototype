<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\PlatformOrder;
use App\Models\Shop;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $shopCount = Shop::count();

        $todayOrders = PlatformOrder::whereDate('ordered_at', today())->count();
        $unshipped   = PlatformOrder::whereNull('shipped_at')->count();
        $todayShipped = PlatformOrder::whereDate('shipped_at', today())->count();
        
        $pendingSync = PlatformOrder::where('sync_status', 'pending')->count();
        $failedOrders = PlatformOrder::where('sync_status', 'failed')->count();

        $shops = Shop::with(['platform', 'platformConnections', 'latestSyncHistory'])->get();

        return view('btoc.dashboard', compact(
            'shopCount',
            'todayOrders',
            'unshipped',
            'todayShipped',
            'pendingSync',
            'failedOrders',
            'shops',
        ));
    }
}
