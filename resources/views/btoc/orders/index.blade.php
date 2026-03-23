@extends('layouts.app')

@section('content')
    <div class="p-2 sm:p-4 w-full mx-auto">

        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="mb-3 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg shadow-sm text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-3 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg shadow-sm text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Tiêu đề & Nút thao tác (Action Buttons) --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <h1 class="text-xl font-semibold text-gray-900">
                管理画面
                <span class="block text-xs text-gray-500 font-normal mt-0.5">Quản lý Đơn hàng</span>
            </h1>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Các nút thao tác hàng loạt (Sẽ submit form bảng dữ liệu) --}}
                {{-- <button type="button" onclick="submitBulkAction('{{ route('btoc.orders.export') }}')"
                    class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-sm font-medium transition shadow-sm">
                    作業指示書出力 (Xuất file chỉ thị)
                </button>
                <button type="button" onclick="submitBulkAction('{{ route('btoc.orders.shipping_notify') }}')"
                    class="px-4 py-2 bg-[#00B900] hover:bg-[#00A000] text-white rounded-lg text-sm font-medium transition shadow-sm">
                    出荷通知 (Thông báo giao hàng)
                </button> --}}
                {{-- Nút Đồng bộ Đơn hàng --}}
                <form action="{{ route('btoc.orders.sync', ['shopId' => 1]) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow">
                        <i class="fas fa-sync-alt mr-2"></i> 注文を同期する (Đồng bộ Next Engine)
                    </button>
                </form>
            </div>
        </div>

        {{-- Bộ lọc tìm kiếm (検索条件) --}}
        {{-- <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm">
            <form method="GET" action="{{ route('btoc.orders.index') }}"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">店舗 (Shop)</label>
                    <select name="shop_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">すべてのショップ (Tất cả)</option>
                        @foreach ($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->shop_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        ステータス (Trạng thái)
                    </label>

                    <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">

                        <option value="">すべて (Tất cả)</option>

                        <option value="{{ \App\Models\NextEngineOrder::ORDER_STATUS_IMPORT_INFO_LACK }}"
                            {{ request('status') == \App\Models\NextEngineOrder::ORDER_STATUS_IMPORT_INFO_LACK ? 'selected' : '' }}>
                            取込情報不足
                        </option>

                        <option value="{{ \App\Models\NextEngineOrder::ORDER_STATUS_EMAIL_IMPORTED }}"
                            {{ request('status') == \App\Models\NextEngineOrder::ORDER_STATUS_EMAIL_IMPORTED ? 'selected' : '' }}>
                            受注メール取込済
                        </option>

                        <option value="{{ \App\Models\NextEngineOrder::ORDER_STATUS_CREATED }}"
                            {{ request('status') == \App\Models\NextEngineOrder::ORDER_STATUS_CREATED ? 'selected' : '' }}>
                            起票済(CSV/手入力)
                        </option>

                        <option value="{{ \App\Models\NextEngineOrder::ORDER_STATUS_INVOICE_WAITING }}"
                            {{ request('status') == \App\Models\NextEngineOrder::ORDER_STATUS_INVOICE_WAITING ? 'selected' : '' }}>
                            納品書印刷待ち
                        </option>

                        <option value="{{ \App\Models\NextEngineOrder::ORDER_STATUS_INVOICE_PRINTING }}"
                            {{ request('status') == \App\Models\NextEngineOrder::ORDER_STATUS_INVOICE_PRINTING ? 'selected' : '' }}>
                            納品書印刷中
                        </option>

                        <option value="{{ \App\Models\NextEngineOrder::ORDER_STATUS_INVOICE_PRINTED }}"
                            {{ request('status') == \App\Models\NextEngineOrder::ORDER_STATUS_INVOICE_PRINTED ? 'selected' : '' }}>
                            納品書印刷済
                        </option>

                        <option value="{{ \App\Models\NextEngineOrder::ORDER_STATUS_SHIPMENT_COMPLETED }}"
                            {{ request('status') == \App\Models\NextEngineOrder::ORDER_STATUS_SHIPMENT_COMPLETED ? 'selected' : '' }}>
                            出荷確定済（完了）
                        </option>

                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">受注日 (Ngày đặt hàng)</label>
                    <div class="flex items-center gap-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 text-sm">
                        <span class="text-gray-500">~</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 text-sm">
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">フリーワード (Tìm kiếm tự do)</label>
                    <input type="text" name="keyword" value="{{ request('keyword') }}"
                        placeholder="受注番号 (Mã ĐH), 購入者名 (Tên người mua)..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 text-sm">
                </div>

                <div class="flex items-end gap-3">
                    <a href="{{ route('btoc.orders.index') }}"
                        class="w-1/3 px-4 py-2 border border-gray-300 text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm text-center transition">
                        クリア
                    </a>
                    <button type="submit"
                        class="w-2/3 px-4 py-2 bg-[#1e293b] hover:bg-black text-white rounded-lg text-sm font-medium transition shadow-sm">
                        検索 (Tìm kiếm)
                    </button>
                </div>
            </form>
        </div> --}}

        {{-- Bảng dữ liệu (受注リスト) --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-sm font-semibold text-gray-800">受注リスト (Danh sách đơn hàng)</h2>
                <span class="text-xs text-gray-500">Tổng cộng: {{ $orders->total() ?? 0 }} đơn</span>
            </div>

            <form id="bulk-action-form" method="POST" action="">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-200 text-[12px] leading-tight">
                                <th class="px-2 py-2 text-center w-8">
                                    <input type="checkbox" id="check-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-2 py-2 font-medium text-gray-700">店舗<br><span class="text-[10px] text-gray-500">Shop</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700">受注番号<br><span class="text-[10px] text-gray-500">Order ID</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700">受注日<br><span class="text-[10px] text-gray-500">Order Date</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700">購入者<br><span class="text-[10px] text-gray-500">Customer</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700">顧客区分<br><span class="text-[10px] text-gray-500">Type</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700 max-w-[120px]">住所<br><span class="text-[10px] text-gray-500">Address</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700">支払方法<br><span class="text-[10px] text-gray-500">Payment</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700">配送方法<br><span class="text-[10px] text-gray-500">Delivery</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700 text-right">商品金額<br><span class="text-[10px] text-gray-500">Goods</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700 text-right">送料<br><span class="text-[10px] text-gray-500">Shipping</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700 ">追跡番号<br><span class="text-[10px] text-gray-500">Tracking No.</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700">ステータス<br><span class="text-[10px] text-gray-500">Status</span></th>
                                <th class="px-2 py-2 font-medium text-gray-700 text-center">操作<br><span class="text-[10px] text-gray-500">Hành động</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($orders ?? [] as $order)
                                <tr class="hover:bg-blue-50 transition text-[12px]">
                                    <td class="px-2 py-1.5 text-center">
                                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-900 truncate max-w-[80px]" title="{{ $order->shop->shop_name ?? '-' }}">
                                        {{ $order->shop->shop_name ?? '-' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-blue-600 font-medium">
                                        {{ $order->receive_order_id }}
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-600">
                                        {{ $order->receive_order_date?->format('Y-m-d') ?? '-' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-900 truncate max-w-[90px]" title="{{ $order->receive_order_creator_name ?? '-' }}">
                                        {{ $order->receive_order_creator_name ?? '-' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-600 truncate max-w-[70px]" title="{{ $order->receive_order_customer_type_name ?? '-' }}">
                                        {{ $order->receive_order_customer_type_name ?? '-' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-600 max-w-[120px] truncate" title="{{ trim(($order->receive_order_purchaser_address1 ?? '') . ' ' . ($order->receive_order_purchaser_address2 ?? '')) }}">
                                        {{ trim(($order->receive_order_purchaser_address1 ?? '') . ' ' . ($order->receive_order_purchaser_address2 ?? '')) ?: '-' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-600 truncate max-w-[70px]" title="{{ $order->receive_order_payment_method_name ?? '-' }}">
                                        {{ $order->receive_order_payment_method_name ?? '-' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-600 truncate max-w-[70px]" title="{{ $order->receive_order_delivery_id ?? '-' }}">
                                        {{ $order->receive_order_delivery_id ?? '-' }}
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-900 text-right font-medium">
                                        ¥{{ number_format($order->receive_order_goods_amount ?? 0) }}
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-900 text-right">
                                        ¥{{ number_format($order->receive_order_delivery_fee_amount ?? 0) }}
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-900 bg-blue-50/30">
                                        <input type="text" id="tracking_number_{{ $order->id }}" value="{{ $order->tracking_number ?? '' }}" placeholder="Nhập mã..." class="w-24 border border-gray-300 rounded py-1 px-1.5 focus:outline-none focus:ring-1 focus:ring-blue-500 text-[12px]">
                                    </td>
                                    <td class="px-2 py-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700 whitespace-nowrap">
                                            {{ $order->receive_order_status_label ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-1.5 text-gray-600 text-center">
                                        <button type="button" onclick="window.location.href='{{ route('btoc.orders.show', $order->id) }}'" class="px-2 py-1 bg-gray-800 hover:bg-black text-white rounded text-[11px] font-medium transition shadow-sm whitespace-nowrap">
                                            見る (Xem)
                                        </button>
                                        <button type="button" onclick="updateTrackingNumber('{{ $order->id }}', '{{ route('btoc.orders.update', $order->id) }}')" class="px-2 py-1 bg-gray-800 hover:bg-black text-white rounded text-[11px] font-medium transition shadow-sm whitespace-nowrap">
                                            登録 (Lưu)
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="px-4 py-8 text-center text-gray-500">
                                        条件に一致する受注はありません。<br>(Không tìm thấy đơn hàng nào phù hợp)
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            
            {{-- Form xóa dùng chung --}}
            <form id="master-delete-form" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            
            {{-- Phân trang --}}
            @if (isset($orders) && $orders->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 text-sm">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Script xử lý UI --}}
    <script>
        document.getElementById('check-all').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        function submitBulkAction(actionUrl) {
            let form = document.getElementById('bulk-action-form');
            let checkedBoxes = document.querySelectorAll('.order-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('対象の受注を選択してください。(Vui lòng chọn ít nhất một đơn hàng)');
                return;
            }
            form.action = actionUrl;
            form.submit();
        }

        function deleteOrder(actionUrl) {
            if (confirm('削除してもよろしいですか？ (Bạn có chắc chắn muốn xóa đơn hàng này không?)')) {
                let form = document.getElementById('master-delete-form');
                form.action = actionUrl;
                form.submit();
            }
        }

        function updateTrackingNumber(orderId, updateUrl) {
            const trackingInput = document.getElementById(`tracking_number_${orderId}`);
            const trackingNumber = trackingInput.value.trim();

            if (!trackingNumber) {
                alert('追跡番号を入力してください。(Vui lòng nhập mã vận đơn)');
                return;
            }

            const metaToken = document.querySelector('meta[name="csrf-token"]');
            const inputToken = document.querySelector('input[name="_token"]');
            const csrfToken = metaToken ? metaToken.getAttribute('content') : (inputToken ? inputToken.value : '');

            if (!csrfToken) {
                alert('Lỗi bảo mật: Không tìm thấy CSRF Token trên trang.');
                return;
            }

            fetch(updateUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    tracking_number: trackingNumber
                })
            })
            .then(response => {
                if (!response.ok) throw new Error('Lỗi mạng hoặc server');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    location.reload(); 
                } else {
                    alert('追跡番号の更新中にエラーが発生しました。(Đã xảy ra lỗi khi lưu)');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('サーバーエラーが発生しました。(Đã xảy ra lỗi máy chủ)');
            });
        }
    </script>
@endsection
