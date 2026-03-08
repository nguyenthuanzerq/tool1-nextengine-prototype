<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Shop;
use App\Services\Btoc\OrderService;
use App\DTO\Btoc\RegisterTrackingDTO;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WorkInstructionExport;


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

        return view('btoc.kanri_gamen', compact('orders', 'shops'));
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
        return Excel::download(new WorkInstructionExport($orders), $fileName);
    }

    /**
     * Đồng bộ đơn hàng từ NextEngine
     */
    public function sync(Request $request)
    {
        // Giai đoạn này ta giả lập phản hồi thành công trước khi ghép nối API thật
        return redirect()->back()->with('success', 'Giả lập phản hồi thành công!');
    }

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
