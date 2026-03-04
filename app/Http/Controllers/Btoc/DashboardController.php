<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Order;

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

        $unshipped = Order::where('status', 'pending')->count();

        $todayShipped = Order::whereDate('shipped_at', today())->count();

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