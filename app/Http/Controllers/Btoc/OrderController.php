<?php

namespace App\Http\Controllers\Btoc;

use App\DTO\Btoc\RegisterTrackingDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Shop;
use App\Services\Btoc\OrderService;
use Illuminate\Support\Facades\Response;
use App\Exports\WorkInstructionExport;
use App\Services\Btoc\MailService;
use Vtiful\Kernel\Excel;
use App\Models\NextEngineOrder;


class OrderController extends Controller
{

    /**
     * Hiển thị danh sách đơn hàng kèm Bộ lọc & Phân trang
     */
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

        // Lọc ra những order có trường tracking_number là rỗng
        $query->where(function ($q) {
            $q->whereNull('tracking_number')->orWhere('tracking_number', '');
        });

        $orders = $query->orderBy('receive_order_date', 'desc')->paginate(20);

        $shops = Shop::all();

        return view('btoc.orders.index', [
            'orders' => $orders,
            'shops' => $shops,
            'filters' => $request->only(['shop_id', 'status', 'date_from', 'date_to', 'keyword'])
        ]);
    }

    /**
     * Hiển thị trang thêm mới đơn hàng
     */

    public function create()
    {
        $isCreate = true;
        $Order = new NextEngineOrder();
        $shops = Shop::all();
        return view('btoc.orders.save', [
            'isCreate' => $isCreate,
            'order' => $Order,
            'shops' => $shops
        ]);
    }

    /**
     * Xử lý luu đơn hàng mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchaser_name' => 'required|string|max:255',
            'receipt_receipt_id' => 'required|string|max:255',
            'shop_id' => 'required|exists:shops,id',
            'receive_order_date' => 'required|date',
            'receive_order_total_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,completed,cancelled',
            'carrier_name' => 'nullable|string|max:255',
            'purchaser_id' => 'nullable|string|max:255',
            'shipping_address' => 'nullable|string',
            'purchaser_phone' => 'nullable|string|max:50',
            'purchaser_email' => 'nullable|email|max:255',
            // 'tracking_number' => 'nullable|string|max:255',
        ], [
            'purchaser_name.required' => '購入者名は空欄にできません.(Tên người mua không được để trống)',
            'receipt_receipt_id.required' => '受注番号は空欄にできません.(Mã đơn hàng không được để trống)',
            'shop_id.required' => '店舗を選択してください.(Vui lòng chọn cửa hàng)',
            'shop_id.exists' => '選択された店舗は存在しません.(Cửa hàng được chọn không tồn tại)',
            'receive_order_date.required' => '注文受け取り日は必須です.(Ngày nhận đơn hàng là bắt buộc)',
            'receive_order_date.date' => '注文受け取り日は有効な日付でなければなりません.(Ngày nhận đơn hàng phải là ngày hợp lệ)',
            'receive_order_total_amount.required' => '合計金額は必須です.(Tổng tiền là bắt buộc)',
            'receive_order_total_amount.numeric' => '合計金額は数値でなければなりません.(Tổng tiền phải là số)',
            'receive_order_total_amount.min' => '合計金額は0以上でなければなりません.(Tổng tiền phải lớn hơn hoặc bằng 0)',
            'status.required' => 'ステータスは必須です.(Trạng thái là bắt buộc)',
            'status.in' => 'ステータスは有効な値でなければなりません (pending, completed, cancelled).(Trạng thái phải là giá trị hợp lệ: pending, completed, cancelled)',
            'carrier_name.string' => '配送業者は文字列でなければなりません.(Công ty vận chuyển phải là chuỗi)',
            'carrier_name.max' => '配送業者は255文字以下でなければなりません.(Công ty vận chuyển không được vượt quá 255 ký tự)',
            'purchaser_id.string' => '購入者IDは文字列でなければなりません.(ID người mua phải là chuỗi)',
            'purchaser_id.max' => '購入者IDは255文字以下でなければなりません.(ID người mua không được vượt quá 255 ký tự)',
            'shipping_address.string' => '配送先住所は文字列でなければなりません.(Địa chỉ người nhận phải là chuỗi)',
            'purchaser_phone.string' => '購入者電話番号は文字列でなければなりません.(Số điện thoại người mua phải là chuỗi)',
            'purchaser_phone.max' => '購入者電話番号は50文字以下でなければなりません.(Số điện thoại người mua không được vượt quá 50 ký tự)',
            'purchaser_email.email' => '購入者メールアドレスは有効なメールアドレスでなければなりません.(Email người mua phải là địa chỉ email hợp lệ)',
            'purchaser_email.max' => '購入者メールアドレスは255文字以下でなければなりません.(Email người mua không được vượt quá 255 ký tự)',
            // 'shipping_delivery_tracking_number.string' => 'お問い合わせ番号は文字列でなければなりません.(Mã vận đơn phải là chuỗi)',
            // 'shipping_delivery_tracking_number.max' => 'お問い合わせ番号は255文字以下でなければなりません.(Mã vận đơn không được vượt quá 255 ký tự)',
        ]);

        $order = NextEngineOrder::create($validated);
        return redirect()->route('btoc.orders.index')->with('success', '新しい注文が正常に追加されました。');
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
            'order' => $order
        ]);
    }

    // public function update(Request $request, $id, MailService $mailService)
    // {
    //     $order = NextEngineOrder::findOrFail($id);

    //     $validated = $request->validate([
    //         'tracking_number' => 'nullable|string|max:255',
    //     ]);

    //     $order->update($validated);
    //     $mailService->sendOrderCreatedMail($order);

    //     return redirect()->route('btoc.orders.index')->with('success', '注文が正常に更新されました。');
    // }

    // Trường update lại tracking number tại danh sách đơn hàng
    public function update(Request $request, $id, MailService $mailService)
    {
        $order = NextEngineOrder::findOrFail($id);

        // Validation lại cho đúng key gửi từ Javascript
        $validated = $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);

        $order->update($validated);
        // Gửi mail khi tracking number được cập nhật
        $mailService->sendOrderCreatedMail($order);

        // Nếu Request gọi từ Javascript (fetch/AJAX), trả về JSON
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
