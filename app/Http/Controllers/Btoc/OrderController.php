<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Mail\ShipmentNotificationMail;
use App\Models\PlatformOrder;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PlatformOrder::with(['shop', 'platform']);

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->filled('platform_id')) {
            $query->where('platform_id', $request->platform_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('ordered_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('ordered_at', '<=', $request->date_to);
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('platform_order_id', 'like', "%{$keyword}%")
                    ->orWhere('buyer_name', 'like', "%{$keyword}%");
            });
        }

        $orders = $query->orderByDesc('ordered_at')->paginate(20)->withQueryString();
        $shops  = Shop::orderBy('shop_name')->get();

        return view('btoc.orders.index', [
            'orders'  => $orders,
            'shops'   => $shops,
            'filters' => $request->only(['shop_id', 'platform_id', 'date_from', 'date_to', 'keyword']),
        ]);
    }

    public function show($id)
    {
        $order = PlatformOrder::with(['shop', 'platform', 'items'])->findOrFail($id);

        return view('btoc.orders.detail', ['order' => $order]);
    }

    public function update(Request $request, $id)
    {
        $order = PlatformOrder::findOrFail($id);

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);

        $order->update($validated);

        $notifyEmail = config('mail.notification_email');
        if ($notifyEmail) {
            Mail::to($notifyEmail)->send(new ShipmentNotificationMail($order->load('shop')));
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => '追跡番号を保存しました。']);
        }

        return redirect()->route('btoc.orders.index')->with('success', '注文が正常に更新されました。');
    }

    public function destroy($id)
    {
        PlatformOrder::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
