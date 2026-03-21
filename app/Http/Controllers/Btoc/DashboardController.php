<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = Order::with('orderProducts')->get();

        return view('btoc.kanri_gamen', compact('orders'));
    }

    public function dashboard()
    {
        $shopCount = Shop::count();

        $todayOrders = Order::whereDate('created_at', today())->count();
        $unshipped = Schema::hasColumn('orders', 'status')
            ? Order::where('status', 'pending')->count()
            : 0;

        $todayShipped = Schema::hasColumn('orders', 'shipped_at')
            ? Order::whereDate('shipped_at', today())->count()
            : 0;

        $shops = Shop::all();

        return view('btoc.dashboard', compact(
            'shopCount',
            'todayOrders',
            'unshipped',
            'todayShipped',
            'shops',
        ));
    }
}
