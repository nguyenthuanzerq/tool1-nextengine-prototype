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


class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Hiển thị danh sách đơn hàng kèm Bộ lọc & Phân trang
     */
    public function index(Request $request)
    {
        $query = Order::with(['shop', 'products']);

        // 1. Lọc theo Shop
        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        // 2. Lọc theo Trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 3. Lọc theo Ngày đặt hàng (Từ ngày)
        if ($request->filled('date_from')) {
            $query->whereDate('receive_order_date', '>=', $request->date_from);
        }

        // 4. Lọc theo Ngày đặt hàng (Đến ngày)
        if ($request->filled('date_to')) {
            $query->whereDate('receive_order_date', '<=', $request->date_to);
        }

        // 5. Lọc theo Từ khóa (Tìm trong Mã đơn hoặc Tên người mua)
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('receipt_receipt_id', 'like', "%{$keyword}%")
                    ->orWhere('purchaser_name', 'like', "%{$keyword}%");
            });
        }

        // Sắp xếp đơn mới nhất lên đầu và phân trang (20 đơn/trang)
        $orders = $query->orderBy('receive_order_date', 'desc')->paginate(20);

        // Lấy danh sách Shop để hiển thị vào thẻ <select>
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
        $Order = new Order();
        $shops = Shop::all();
        return view('btoc.orders.save',[
            'isCreate' => $isCreate,
            'order' => $Order,
            'shops' => $shops
        ]);
    }

    /**
     * Xử lý luu đơn hàng mới
     */
    public function store(Request $request, MailService $mailService){
        $validated = $request->validate([
            'purchaser_name' => 'required|string|max:255',
            'receipt_receipt_id' => 'required|string|max:255',
            'shop_id' => 'required|exists:shops,id',
            'receive_order_date' => 'required|date',
            'receive_order_total_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,completed,cancelled',
            // 'tracking_number' => 'nullable|string|max:255',
        ], [
            'purchaser_name.required' => '購入者名は空欄にできません。(Tên người mua không được để trống)',
            'receipt_receipt_id.required' => '受注番号は空欄にできません。(Mã đơn hàng không được để trống)',
            'shop_id.required' => '店舗を選択してください。(Vui lòng chọn cửa hàng)',
            'shop_id.exists' => '選択された店舗は存在しません。(Cửa hàng được chọn không tồn tại)',
            'receive_order_date.required' => '注文受け取り日は必須です。(Ngày nhận đơn hàng là bắt buộc)',
            'receive_order_date.date' => '注文受け取り日は有効な日付でなければなりません。(Ngày nhận đơn hàng phải là ngày hợp lệ)',
            'receive_order_total_amount.required' => '合計金額は必須です。(Tổng tiền là bắt buộc)',
            'receive_order_total_amount.numeric' => '合計金額は数値でなければなりません。(Tổng tiền phải là số)',
            'receive_order_total_amount.min' => '合計金額は0以上でなければなりません。(Tổng tiền phải lớn hơn hoặc bằng 0)',
            'status.required' => 'ステータスは必須です。(Trạng thái là bắt buộc)',
            'status.in' => 'ステータスは有効な値でなければなりません (pending, completed, cancelled)。(Trạng thái phải là giá trị hợp lệ: pending, completed, cancelled)',
            // 'shipping_delivery_tracking_number.string' => 'お問い合わせ番号は文字列でなければなりません。(Mã vận đơn phải là chuỗi)',
            // 'shipping_delivery_tracking_number.max' => 'お問い合わせ番号は255文字以下でなければなりません。(Mã vận đơn không được vượt quá 255 ký tự)',
        ]);

        $order = Order::create($validated);
        // $mailService->sendOrderCreatedMail($order);

        return redirect()->route('btoc.orders.index')->with('success', '新しい注文が正常に追加されました。');
    }

    public function show($id)
    {
        $order = Order::with(['shop', 'products'])->findOrFail($id);
        return view('btoc.orders.detail', ['order' => $order]);
    }

    public function edit($id)
    {
        $isCreate = false;
        $order = Order::findOrFail($id);
        $shops = Shop::all();
        return view('btoc.orders.save',[
            'isCreate' => $isCreate,
            'order' => $order,
            'shops' => $shops
        ]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'purchaser_name' => 'required|string|max:255',
            'shop_id' => 'required|exists:shops,id',
            'receipt_receipt_id' => 'required|string|max:255',
            'receive_order_date' => 'required|date',
            'receive_order_total_amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,completed,cancelled',
            'shipping_delivery_tracking_number' => 'nullable|string|max:255',
        ]);

        $order->update($validated);

        return redirect()->route('btoc.orders.index')->with('success', '注文が正常に更新されました。');
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->route('btoc.orders.index')->with('success', '注文が正常に削除されました。');
    }

    /**
     * Xuất danh sách đơn hàng ra file Excel (作業指示書)
     */
    public function exportInstruction(Request $request)
    {
        $orderIds = $request->input('order_ids', []);

        if (empty($orderIds)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất một đơn hàng để xuất file.');
        }

        // Lấy dữ liệu
        $orders = Order::with(['shop', 'products'])->whereIn('id', $orderIds)->get();

        // Tạo tên file Excel đuôi .xlsx
        $fileName = '作業指示書_' . date('Ymd_His') . '.xlsx';

        // Gọi class Export để tải file về
        // return Excel::download(new WorkInstructionExport($orders), $fileName);
    }

    /**
     * Đồng bộ đơn hàng từ NextEngine
     */
    

    /**
     * Cập nhật trạng thái xuất hàng hàng loạt
     */

    public function shippingNotify(Request $request)
    {
        $orderIds = $request->input('order_ids', []);

        if (empty($orderIds)) {
            return redirect()->back()->with('error', '少なくとも 1 つの注文を選択してください。');
        }

        Order::whereIn('id', $orderIds)->update([
            'status' => 'shipped',
            'shipped_at' => now()
        ]);

        return redirect()->back()->with('success', count($orderIds) . ' 注文は「配達済み」に更新されました。');
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
