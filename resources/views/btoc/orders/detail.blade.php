@extends('layouts.app')

@section('content')
<div class="max-w-[1440px]">
    <a href="{{ route('btoc.orders.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 mb-5 transition">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
        受注一覧に戻る
    </a>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">注文詳細</h1>
            <p class="text-sm text-gray-500 mt-0.5">Order Detail</p>
        </div>
    </div>

    {{-- Order info grid --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-0 mb-6 overflow-hidden">
        {{-- Column 1: Order Info --}}
        <div class="p-6 md:border-r border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-100">注文情報 / Order Info</h3>
            <dl class="divide-y divide-gray-100">
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">プラットフォーム</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->platform->name ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">ショップ</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->shop->shop_name ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">注文番号</dt>
                    <dd class="text-sm text-gray-700 col-span-2 font-mono">{{ $order->platform_order_id ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">受注日</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->ordered_at?->format('Y-m-d H:i') ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">ステータス</dt>
                    <dd class="text-sm text-gray-700 col-span-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $order->platform_order_status ?? '-' }}
                        </span>
                    </dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">支払方法</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->payment_method ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">商品金額</dt>
                    <dd class="text-sm text-gray-700 col-span-2">¥{{ number_format($order->goods_amount ?? 0) }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">送料</dt>
                    <dd class="text-sm text-gray-700 col-span-2">¥{{ number_format($order->delivery_fee ?? 0) }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">合計金額</dt>
                    <dd class="text-sm font-semibold text-gray-900 col-span-2">¥{{ number_format($order->total_amount ?? 0) }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">追跡番号</dt>
                    <dd class="text-sm text-blue-600 col-span-2 font-mono">{{ $order->tracking_number ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Column 2: Customer & Delivery --}}
        <div class="p-6">
            <h3 class="text-sm font-semibold text-gray-800 mb-3 pb-2 border-b border-gray-100">購入者・配送情報 / Customer &amp; Delivery</h3>
            <dl class="divide-y divide-gray-100">
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">購入者名</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->buyer_name ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">Email</dt>
                    <dd class="text-sm text-gray-700 col-span-2 break-all">{{ $order->buyer_email ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">電話番号</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->buyer_phone ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">郵便番号</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->buyer_zip ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">住所</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->buyer_address ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">配送先名</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->delivery_name ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">配送先住所</dt>
                    <dd class="text-sm text-gray-700 col-span-2">
                        {{ $order->delivery_zip ? '〒' . $order->delivery_zip . ' ' : '' }}{{ $order->delivery_address ?? '-' }}
                    </dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">配送方法</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->delivery_method ?? '-' }}</dd>
                </div>
                <div class="py-2.5 grid grid-cols-3 gap-4">
                    <dt class="text-xs font-medium text-gray-500 col-span-1 self-center">出荷日</dt>
                    <dd class="text-sm text-gray-700 col-span-2">{{ $order->shipped_at?->format('Y-m-d') ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Line items --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm max-w-4xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-semibold text-gray-800">注文明細 / Order Items</h3>
        </div>

        @if($order->items->isEmpty())
            <div class="px-6 py-10 text-center">
                <p class="text-sm text-gray-500">明細データがありません。同期後に表示されます。</p>
                <p class="text-xs text-gray-400 mt-1">No item data. Will appear after sync.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU / 商品コード</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">商品名 / Product</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">数量 / Qty</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">単価 / Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">小計 / Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($order->items as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4 text-sm text-gray-700 font-mono">{{ $item->product_code ?? '-' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $item->product_name ?? '-' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700 text-right">{{ $item->quantity }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700 text-right">¥{{ number_format($item->unit_price) }}</td>
                            <td class="px-4 py-4 text-sm font-medium text-gray-900 text-right">¥{{ number_format($item->total_price) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-200">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-right text-sm font-medium text-gray-700">合計 / Total</td>
                            <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                ¥{{ number_format($order->items->sum('total_price')) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
