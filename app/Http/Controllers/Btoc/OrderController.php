<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\NextEngineOrder;
use App\Models\Order;
use App\Models\Shop;
use App\Services\Btoc\MailService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = NextEngineOrder::with('shop');

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->status) {
            $query->where('receive_order_order_status_id', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('receive_order_import_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('receive_order_date', '<=', $request->date_to);
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('receive_order_id', 'like', "%{$keyword}%")
                    ->orWhere('receive_order_creator_name', 'like', "%{$keyword}%");
            });
        }

        $query->where(function ($q) {
            $q->whereNull('tracking_number')->orWhere('tracking_number', '');
        });

        $orders = $query->orderBy('receive_order_date', 'desc')->paginate(20);

        $shops = Shop::all();

        return view('btoc.orders.index', [
            'orders' => $orders,
            'shops' => $shops,
            'filters' => $request->only(['shop_id', 'status', 'date_from', 'date_to', 'keyword']),
        ]);
    }

    public function create()
    {
        $isCreate = true;
        $Order = new Order;
        $shops = Shop::all();

        return view('btoc.orders.save', [
            'isCreate' => $isCreate,
            'order' => $Order,
            'shops' => $shops,
        ]);
    }

    public function show($id)
    {
        $order = NextEngineOrder::with('shop')->findOrFail($id);

        return view('btoc.orders.detail', ['order' => $order]);
    }

    public function edit($id)
    {
        $order = NextEngineOrder::with('shop')->findOrFail($id);

        return view('btoc.orders.save', [
            'order' => $order,
        ]);
    }

    public function update(Request $request, $id, MailService $mailService)
    {
        $order = NextEngineOrder::findOrFail($id);

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);

        $order->update($validated);
        $mailService->sendOrderCreatedMail($order);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Lưu thành công']);
        }

        return redirect()->route('btoc.orders.index')->with('success', '注文が正常に更新されました。');
    }

    public function destroy($id)
    {
        $order = NextEngineOrder::findOrFail($id);
        $order->delete();

        return redirect()->route('btoc.orders.index')->with('success', '注文が正常に削除されました。');
    }
}
