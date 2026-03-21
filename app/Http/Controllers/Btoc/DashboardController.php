<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\NextEngineOrder;
use App\Models\Shop;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $orders = NextEngineOrder::with('orderProducts')->get();

        return view('btoc.orders.index', compact('orders'));
    }

    public function dashboard()
    {
        $shopCount = Shop::count();

        $todayOrders = NextEngineOrder::whereDate('created_at', today())->count();

        // Defensive: chỉ query nếu cột tồn tại trong DB (tránh 500 khi migration chưa chạy)
        // $unshipped = Schema::hasColumn('orders', 'status')
        //     ? NextEngineOrder::where('status', 'pending')->count()
        //     : 0;

        $unshipped = 10; // Tạm thời hardcode để test giao diện, sẽ xóa sau khi DB đã có dữ liệu

        // $todayShipped = Schema::hasColumn('orders', 'shipped_at')
        //     ? NextEngineOrder::whereDate('shipped_at', today())->count()
        //     : 0;

        $todayShipped = 5; // Tạm thời hardcode để test giao diện, sẽ xóa sau khi DB đã có dữ liệu

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