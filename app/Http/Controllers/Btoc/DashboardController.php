<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Order;
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

        // Defensive: chỉ query nếu cột tồn tại trong DB (tránh 500 khi migration chưa chạy)
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