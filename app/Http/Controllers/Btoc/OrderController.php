<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Services\Btoc\OrderService;
use App\DTO\Btoc\RegisterTrackingDTO;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = Order::with('orderProducts')->get();

        return view('btoc.kanri_gamen', compact('orders'));
    }

    public function registerTracking(Request $request)
    {
       $validated = $request->validate([
    'order_id'        => 'required|exists:orders,id',
    'tracking_number' => 'required|string|max:255',
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