@extends('layouts.app')

@section('content')
    <div class="max-w-[1440px] flex flex-col h-full">

        @if (session('success'))
            <div class="mb-4 flex-shrink-0 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 flex-shrink-0 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                {{ session('error') }}</div>
        @endif

        <div class="mb-6 flex-shrink-0 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">受注管理</h1>
                <p class="text-sm text-gray-500 mt-0.5">Order Management</p>
            </div>
            <span class="text-sm text-gray-400">{{ $orders->total() ?? 0 }} 件</span>
        </div>

        {{-- Filter bar --}}
        <form method="GET" action="{{ route('btoc.orders.index') }}"
            class="flex-shrink-0 bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">

            <div class="flex-1 min-w-[150px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">店舗 / Shop</label>
                <select name="shop_id"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">すべての店舗</option>
                    @foreach ($shops as $shop)
                        <option value="{{ $shop->id }}"
                            {{ ($filters['shop_id'] ?? '') == $shop->id ? 'selected' : '' }}>
                            {{ $shop->shop_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">開始日 / From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex-1 min-w-[120px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">終了日 / To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1">キーワード / Keyword</label>
                <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}" placeholder="注文番号・購入者名"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    検索
                </button>
                <a href="{{ route('btoc.orders.index') }}"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                    クリア
                </a>
            </div>
        </form>

        {{-- Table --}}
        <div class="flex-1 flex flex-col min-h-0 bg-white rounded-xl border border-gray-200 shadow-sm">
            <form id="bulk-action-form" method="POST" action="" class="flex-1 flex flex-col min-h-0">
                @csrf
                <div class="flex-1 overflow-auto min-h-0">
                    <table class="w-full text-left table-fixed">
                        <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                            <tr class="text-gray-700">
                                <th class="px-1 py-2 w-8 text-center">
                                    <input type="checkbox" id="check-all"
                                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-1 py-2 w-[7%] text-[11px] font-bold leading-tight">
                                    ショップ名
                                    <span class="block font-normal text-[10px] text-gray-500">(Shop Name)</span>
                                </th>
                                <th class="px-1 py-2 w-[6%] text-[11px] font-bold leading-tight">
                                    ShopID
                                </th>
                                <th class="px-1 py-2 w-[9%] text-[11px] font-bold leading-tight">
                                    注文ID
                                    <span class="block font-normal text-[10px] text-gray-500">(Order ID)</span>
                                </th>
                                <th class="px-1 py-2 w-[11%] text-[11px] font-bold leading-tight">
                                    商品名
                                    <span class="block font-normal text-[10px] text-gray-500">(Product Name)</span>
                                </th>
                                <th class="px-1 py-2 w-[7%] text-[11px] font-bold leading-tight">
                                    配送会社
                                    <span class="block font-normal text-[10px] text-gray-500">(Carrier)</span>
                                </th>
                                <th class="px-1 py-2 w-[10%] text-[11px] font-bold leading-tight">
                                    発送番号
                                    <span class="block font-normal text-[10px] text-gray-500">(Tracking No.)</span>
                                </th>
                                <th class="px-1 py-2 w-[7%] text-[11px] font-bold leading-tight">
                                    注文者ID
                                    <span class="block font-normal text-[10px] text-gray-500">(Buyer ID)</span>
                                </th>
                                <th class="px-1 py-2 w-[7%] text-[11px] font-bold leading-tight">
                                    注文者名
                                    <span class="block font-normal text-[10px] text-gray-500">(Buyer Name)</span>
                                </th>
                                <th class="px-1 py-2 w-[12%] text-[11px] font-bold leading-tight">
                                    配送先住所
                                    <span class="block font-normal text-[10px] text-gray-500">(Shipping Address)</span>
                                </th>
                                <th class="px-1 py-2 w-[8%] text-[11px] font-bold leading-tight">
                                    電話番号
                                    <span class="block font-normal text-[10px] text-gray-500">(Phone)</span>
                                </th>
                                <th class="px-1 py-2 w-[10%] text-[11px] font-bold leading-tight">
                                    メール
                                    <span class="block font-normal text-[10px] text-gray-500">(Email)</span>
                                </th>
                                <th class="px-1 py-2 w-[5%] text-center text-[11px] font-bold leading-tight">
                                    操作
                                    <span class="block font-normal text-[10px] text-gray-500">(Action)</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($orders ?? [] as $order)
                                <tr class="hover:bg-gray-50 transition border-b border-gray-100">
                                    <td class="px-1 py-3 text-center align-middle">
                                        <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                                            class="order-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    </td>

                                    <td class="px-1 py-3 text-[11px] text-gray-700 truncate align-middle"
                                        title="{{ $order->shop->shop_name ?? '-' }}">
                                        {{ $order->shop->shop_name ?? '-' }}
                                    </td>

                                    <td class="px-1 py-3 text-[11px] text-gray-500 font-mono align-middle">
                                        {{ $order->shop->shop_code ?? '-' }}
                                    </td>

                                    <td class="px-1 py-3 align-middle">
                                        <div class="text-blue-600 font-mono text-[11px] truncate"
                                            title="{{ $order->platform_order_id }}">
                                            {{ $order->platform_order_id }}
                                        </div>
                                    </td>

                                    <td class="px-1 py-3 text-[11px] text-gray-700 align-middle">
                                        <div class="line-clamp-2"
                                            title="{{ $order->items->first()->goods_name ?? 'N/A' }}">
                                            {{ $order->items->first()->goods_name ?? 'N/A' }}
                                        </div>
                                    </td>

                                    <td class="px-1 py-3 text-[11px] text-gray-700 truncate align-middle"
                                        title="{{ $order->delivery_method ?? '-' }}">
                                        {{ $order->delivery_method ?? '-' }}
                                    </td>

                                    <td class="px-1 py-3 align-middle">
                                        <input type="text" id="tracking_number_{{ $order->id }}"
                                            value="{{ $order->tracking_number ?? '' }}" placeholder="入力"
                                            class="w-full rounded border border-gray-300 px-1.5 py-1 text-[11px] focus:outline-none focus:ring-1 focus:ring-blue-500 shadow-sm">
                                    </td>

                                    <td class="px-1 py-3 text-[11px] text-gray-700 font-mono truncate align-middle"
                                        title="{{ $order->buyer_id ?? '-' }}">
                                        {{ $order->buyer_id ?? '-' }}
                                    </td>

                                    <td class="px-1 py-3 text-[11px] text-gray-800 font-medium truncate align-middle"
                                        title="{{ $order->buyer_name ?? '-' }}">
                                        {{ $order->buyer_name ?? '-' }}
                                    </td>

                                    <td class="px-1 py-3 text-[11px] text-gray-700 align-middle">
                                        <div class="line-clamp-2 leading-tight"
                                            title="{{ $order->buyer_address ?? ($order->delivery_address ?? '-') }}">
                                            {{ $order->buyer_address ?? ($order->delivery_address ?? '-') }}
                                        </div>
                                    </td>

                                    <td class="px-1 py-3 text-[11px] text-gray-600 truncate align-middle"
                                        title="{{ $order->buyer_phone ?? '-' }}">
                                        {{ $order->buyer_phone ?? '-' }}
                                    </td>

                                    <td class="px-1 py-3 text-[11px] text-gray-500 truncate align-middle"
                                        title="{{ $order->buyer_email ?? '-' }}">
                                        {{ $order->buyer_email ?? '-' }}
                                    </td>

                                    <td class="px-1 py-3 align-middle text-center">
                                        <div class="flex flex-col gap-1">
                                            <button type="button" id="register-btn-{{ $order->id }}"
                                                onclick="updateTrackingNumber('{{ $order->id }}', '{{ route('btoc.orders.update', $order->id) }}')"
                                                class="w-full inline-flex justify-center items-center px-1 py-1.5 rounded text-[10px] font-bold bg-[#1e293b] hover:bg-gray-800 text-white transition shadow-sm">
                                                登録
                                            </button>

                                            <a href="{{ route('btoc.orders.show', $order->id) }}"
                                                class="w-full inline-flex justify-center items-center px-1 py-1.5 rounded text-[10px] font-bold bg-blue-600 hover:bg-blue-700 text-white transition shadow-sm">
                                                詳細
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="px-4 py-8 text-center text-sm text-gray-400">
                                        条件に一致する受注はありません。(No matching orders found.)
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            {{-- Delete form (shared) --}}
            <form id="master-delete-form" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>

            {{-- Pagination --}}
            <div
                class="flex-shrink-0 px-4 py-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                <span>{{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} / {{ $orders->total() }} 件</span>
                {{ $orders->links() }}
            </div>
        </div>
    </div>

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
                alert('対象の受注を選択してください。/ Please select at least one order.');
                return;
            }
            form.action = actionUrl;
            form.submit();
        }

        function deleteOrder(actionUrl) {
            if (confirm('削除してもよろしいですか？ / Are you sure you want to delete this order?')) {
                let form = document.getElementById('master-delete-form');
                form.action = actionUrl;
                form.submit();
            }
        }

        function updateTrackingNumber(orderId, updateUrl) {
            const trackingInput = document.getElementById(`tracking_number_${orderId}`);
            const trackingNumber = trackingInput.value.trim();
            const btn = document.getElementById(`register-btn-${orderId}`);

            if (!trackingNumber) {
                alert('追跡番号を入力してください。/ Please enter a tracking number.');
                return;
            }

            const metaToken = document.querySelector('meta[name="csrf-token"]');
            const inputToken = document.querySelector('input[name="_token"]');
            const csrfToken = metaToken ? metaToken.getAttribute('content') : (inputToken ? inputToken.value : '');

            if (!csrfToken) {
                alert('セキュリティエラー: CSRF Token not found.');
                return;
            }

            btn.disabled = true;
            btn.textContent = '送信中…';
            btn.classList.add('opacity-60', 'cursor-not-allowed');

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
                    if (!response.ok) throw new Error('server error');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        btn.textContent = '完了';
                        btn.classList.remove('bg-white', 'border-gray-300', 'text-gray-700', 'hover:bg-gray-50',
                            'opacity-60', 'cursor-not-allowed');
                        btn.classList.add('bg-green-600', 'text-white', 'border-green-600');
                        setTimeout(() => location.reload(), 800);
                    } else {
                        btn.disabled = false;
                        btn.textContent = '登録';
                        btn.classList.remove('opacity-60', 'cursor-not-allowed');
                        alert('追跡番号の更新中にエラーが発生しました。/ Failed to save.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    btn.disabled = false;
                    btn.textContent = '登録';
                    btn.classList.remove('opacity-60', 'cursor-not-allowed');
                    alert('サーバーエラーが発生しました。/ A server error occurred.');
                });
        }
    </script>
@endsection
