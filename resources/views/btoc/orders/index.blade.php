@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] flex flex-col h-full">

    @if (session('success'))
        <div class="mb-4 flex-shrink-0 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex-shrink-0 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
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
            <select name="shop_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">すべての店舗</option>
                @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}" {{ ($filters['shop_id'] ?? '') == $shop->id ? 'selected' : '' }}>
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
            <input type="text" name="keyword" value="{{ $filters['keyword'] ?? '' }}"
                placeholder="注文番号・購入者名"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="flex gap-2">
            <button type="submit"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" /><path d="m21 21-4.35-4.35" />
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
                <table class="w-full text-left">
                    <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 w-8">
                                <input type="checkbox" id="check-all" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                注文番号
                                <span class="block font-normal normal-case text-gray-400">Order ID</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ショップ
                                <span class="block font-normal normal-case text-gray-400">Shop</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                購入者
                                <span class="block font-normal normal-case text-gray-400">Customer</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                受注日
                                <span class="block font-normal normal-case text-gray-400">Order Date</span>
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                合計
                                <span class="block font-normal normal-case text-gray-400">Total</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                追跡番号
                                <span class="block font-normal normal-case text-gray-400">Tracking</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ステータス
                                <span class="block font-normal normal-case text-gray-400">Status</span>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders ?? [] as $order)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <div class="font-medium text-blue-600 font-mono text-xs">{{ $order->platform_order_id }}</div>
                                    @if($order->platform)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 mt-0.5">
                                            {{ $order->platform->name }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <div class="font-medium truncate max-w-[120px]" title="{{ $order->shop->shop_name ?? '-' }}">{{ $order->shop->shop_name ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <div class="font-medium truncate max-w-[110px]" title="{{ $order->buyer_name ?? '-' }}">{{ $order->buyer_name ?? '-' }}</div>
                                    @if($order->customer_type)
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $order->customer_type }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    {{ $order->ordered_at?->format('Y-m-d') ?? '-' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 text-right font-medium">
                                    ¥{{ number_format($order->total_amount ?? 0) }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <input type="text" id="tracking_number_{{ $order->id }}"
                                           value="{{ $order->tracking_number ?? '' }}"
                                           placeholder="追跡番号"
                                           class="w-36 rounded-lg border border-gray-300 px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 whitespace-nowrap">
                                        {{ $order->platform_order_status ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                                id="register-btn-{{ $order->id }}"
                                                onclick="updateTrackingNumber('{{ $order->id }}', '{{ route('btoc.orders.update', $order->id) }}')"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                            登録
                                        </button>
                                        <a href="{{ route('btoc.orders.show', $order->id) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                                            詳細
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-sm text-gray-400">
                                    条件に一致する受注はありません。
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
        <div class="flex-shrink-0 px-4 py-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
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
                btn.classList.remove('bg-white', 'border-gray-300', 'text-gray-700', 'hover:bg-gray-50', 'opacity-60', 'cursor-not-allowed');
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
