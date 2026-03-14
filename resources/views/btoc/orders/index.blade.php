@extends('layouts.app')

@section('content')
    <div class="p-6 max-w-[1440px] mx-auto">

        {{-- Alert Messages --}}
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg shadow-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Tiêu đề & Nút thao tác (Action Buttons) --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-900">
                管理画面
                <span class="block text-sm text-gray-500 font-normal mt-1">Quản lý Đơn hàng</span>
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
                <button type="button" onclick="window.location.href='{{ route('btoc.orders.create') }}'"
                    class="px-4 py-2 bg-[#00B900] hover:bg-[#00A000] text-white rounded-lg text-sm font-medium transition shadow-sm">
                    注文を追加 (Thêm đơn hàng )
                </button>
            </div>
        </div>

        {{-- Bộ lọc tìm kiếm (検索条件) --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm">
            <form method="GET" action="{{ route('btoc.orders.index') }}"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                {{-- Chọn Shop --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">店舗 (Shop)</label>
                    <select name="shop_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">すべてのショップ (Tất cả)</option>
                        {{-- Sẽ load động từ Controller sau: --}}
                        @foreach ($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                {{ $shop->shop_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Trạng thái --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ステータス (Trạng thái)</label>
                    <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="">すべて (Tất cả)</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>新規受付 (Mới nhận)
                        </option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>印刷待ち (Chờ in)
                        </option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>出荷済み (Đã giao)
                        </option>
                    </select>
                </div>

                {{-- Khoảng thời gian (Ngày đặt hàng) --}}
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

                {{-- Thanh tìm kiếm --}}
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">フリーワード (Tìm kiếm tự do)</label>
                    <input type="text" name="keyword" value="{{ request('keyword') }}"
                        placeholder="受注番号 (Mã ĐH), 購入者名 (Tên người mua)..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 text-sm">
                </div>

                {{-- Nút Submit Filter --}}
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
        </div>

        {{-- Bảng dữ liệu (受注リスト) --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-base font-semibold text-gray-800">受注リスト (Danh sách đơn hàng)</h2>
                <span class="text-sm text-gray-500">Tổng cộng: {{ $orders->total() ?? 0 }} đơn</span>
            </div>

            {{-- Form xử lý thao tác hàng loạt --}}
            <form id="bulk-action-form" method="POST" action="">
                @csrf
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-100 border-b border-gray-200 text-sm">
                                <th class="px-4 py-3 text-center w-12">
                                    <input type="checkbox" id="check-all"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">店舗<br><span
                                        class="text-xs text-gray-500">Shop</span></th>
                                <th class="px-4 py-3 font-medium text-gray-700">受注番号<br><span
                                        class="text-xs text-gray-500">Mã đơn hàng</span></th>
                                <th class="px-4 py-3 font-medium text-gray-700">受注日<br><span
                                        class="text-xs text-gray-500">Ngày đặt</span></th>

                                {{-- Thêm các cột mới --}}
                                <th class="px-4 py-3 font-medium text-gray-700">注文者 ID<br><span
                                        class="text-xs text-gray-500">ID Người mua</span></th>
                                <th class="px-4 py-3 font-medium text-gray-700">購入者名<br><span
                                        class="text-xs text-gray-500">Tên người mua</span></th>
                                <th class="px-4 py-3 font-medium text-gray-700">電話番号<br><span
                                        class="text-xs text-gray-500">SĐT</span></th>
                                <th class="px-4 py-3 font-medium text-gray-700">メールアドレス<br><span
                                        class="text-xs text-gray-500">Email</span></th>
                                <th class="px-4 py-3 font-medium text-gray-700 max-w-[150px]">配送先住所<br><span
                                        class="text-xs text-gray-500">Địa chỉ</span></th>
                                <th class="px-4 py-3 font-medium text-gray-700">配送会社<br><span
                                        class="text-xs text-gray-500">Hãng V/C</span></th>

                                <th class="px-4 py-3 font-medium text-gray-700 text-right">合計金額<br><span
                                        class="text-xs text-gray-500">Tổng tiền</span></th>
                                <th class="px-4 py-3 font-medium text-gray-700 text-right">追跡番号<br><span
                                        class="text-xs text-gray-500">Tracking Number</span></th>
                                <th class="px-4 py-3 font-medium text-gray-700">ステータス<br><span
                                        class="text-xs text-gray-500">Trạng thái</span></th>
                                <th class="px-4 py-3 font-medium text-gray-700">お問い合わせ番号<br><span
                                        class="text-xs text-gray-500">Hành động</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($orders ?? [] as $order)
                                <tr class="hover:bg-blue-50 transition">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                                            class="order-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->shop->shop_name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-blue-600 font-medium cursor-pointer hover:underline">
                                        {{ $order->receipt_receipt_id }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $order->receive_order_date ? $order->receive_order_date->format('Y-m-d') : '-' }}
                                    </td>

                                    {{-- Đổ dữ liệu các cột mới --}}
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $order->purchaser_id ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $order->purchaser_name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $order->purchaser_phone ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $order->purchaser_email ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 max-w-[150px] truncate"
                                        title="{{ $order->shipping_address }}">{{ $order->shipping_address ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ $order->carrier_name ?? '-' }}</td>

                                    <td class="px-4 py-3 text-sm text-gray-900 text-right font-medium">
                                        ¥{{ number_format($order->receive_order_total_amount) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">
                                        {{ $order->shipping_delivery_tracking_number ?? '未登録 (Chưa có)' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @php
                                            $statusClass = match ($order->status) {
                                                '新規受付', 'pending' => 'bg-red-100 text-red-700',
                                                '印刷待ち', 'processing' => 'bg-yellow-100 text-yellow-700',
                                                '出荷済み', 'shipped' => 'bg-green-100 text-green-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                            $statusLabel = match ($order->status) {
                                                'pending' => '新規受付',
                                                'processing' => '印刷待ち',
                                                'shipped' => '出荷済み',
                                                default => $order->status,
                                            };
                                        @endphp
                                        <span
                                            class="px-2 py-1 rounded text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                onclick="window.location.href='{{ route('btoc.orders.show', $order->id) }}'"
                                                class="px-3 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded text-xs font-medium transition">詳細</button>
                                            <button type="button"
                                                onclick="window.location.href='{{ route('btoc.orders.edit', $order->id) }}'"
                                                class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-600 rounded text-xs font-medium transition">編集</button>
                                            <button type="button"
                                                onclick="deleteOrder('{{ route('btoc.orders.destroy', $order->id) }}')"
                                                class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded text-xs font-medium transition">
                                                削除
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="px-4 py-12 text-center text-gray-500">条件に一致する受注はありません。
                                        (Không tìm thấy đơn hàng nào phù hợp)
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
            {{-- Form xóa dùng chung (Đặt ngoài bảng) --}}
            <form id="master-delete-form" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
            {{-- Phân trang --}}
            @if (isset($orders) && $orders->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Script xử lý UI --}}
    <script>
        // 1. Xử lý logic Check All
        document.getElementById('check-all').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // 2. Xử lý chuyển đổi Action Form
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

        // 3. Xử lý chức năng Xóa đơn hàng lẻ
        function deleteOrder(actionUrl) {
            if (confirm('削除してもよろしいですか？ (Bạn có chắc chắn muốn xóa đơn hàng này không?)')) {
                let form = document.getElementById('master-delete-form');
                form.action = actionUrl;
                form.submit();
            }
        }
    </script>
@endsection
