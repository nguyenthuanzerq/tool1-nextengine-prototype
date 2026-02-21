<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Btoc\OrderService;
use App\DTO\Btoc\RegisterTrackingDTO;
use App\Models\Order;
use App\Models\Shop;

class BtocOrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
       $orders = Order::with('orderProducts')->get();

    return view('btoc.index', compact('orders'));
    }

    public function dashboard()
{
      $shopCount = \App\Models\Shop::count();

    $todayOrders = \App\Models\Order::whereDate('created_at', today())->count();

    $unshipped = \App\Models\Order::where('status', 'pending')->count();

    $todayShipped = \App\Models\Order::whereDate('shipped_at', today())->count();

    $shops = Shop::all();

    return view('btoc.dashboard', compact(
        'shopCount',
        'todayOrders',
        'unshipped',
        'todayShipped',
        'shops',
    ));
}

    public function registerTracking(Request $request)
    {
        $validated = $request->validate([
            'order_id'       => 'required|integer',
            'tracking_number'=> 'required|string|max:255',
        ]);

        $dto = new RegisterTrackingDTO(
            (int) $validated['order_id'],
            (string) $validated['tracking_number']
        );

        $this->orderService->registerTracking($dto);

       return redirect()
    ->route('btoc.index')
    ->with('success', '発送番号を登録しました。');
    }
}
