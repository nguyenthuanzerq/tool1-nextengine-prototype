@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] flex flex-col h-full">

    <div class="mb-6 flex-shrink-0 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">在庫管理</h1>
            <p class="text-sm text-gray-500 mt-0.5">各プラットフォームから同期された在庫データ / Raw stock data synced from each platform</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 flex-shrink-0 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex-shrink-0 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('btoc.inventory') }}"
        class="flex-shrink-0 bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4 flex flex-wrap gap-3 items-end">

        <div class="flex-1 min-w-[160px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">店舗 / Shop</label>
            <select name="shop_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">すべての店舗</option>
                @foreach ($shops as $shop)
                    <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                        {{ $shop->shop_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[160px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">プラットフォーム / Platform</label>
            <select name="platform_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">すべてのプラットフォーム</option>
                @foreach ($platforms as $platform)
                    <option value="{{ $platform->id }}" {{ request('platform_id') == $platform->id ? 'selected' : '' }}>
                        {{ $platform->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[160px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">商品コード / Product Code</label>
            <input type="text" name="product_code" value="{{ request('product_code') }}"
                placeholder="例: SKU-001"
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
            <a href="{{ route('btoc.inventory') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                クリア
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div class="flex-1 flex flex-col min-h-0 bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="flex-shrink-0 px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-800">プラットフォーム在庫</h2>
                <p class="text-xs text-gray-400">Platform Inventory</p>
            </div>
            <span class="text-xs text-gray-400">{{ $items->total() }} 件</span>
        </div>

        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">platform</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">store</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Number of items in inventory</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Utilize the possibility</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Booked</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Final Class</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-4 text-sm text-gray-700 font-mono text-xs">
                                <div>{{ $item->product_code }}</div>
                                @if($item->manage_number || $item->variant_id)
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        {{ $item->manage_number ? "M: {$item->manage_number}" : '' }}
                                        {{ $item->variant_id ? "V: {$item->variant_id}" : '' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $item->product_name ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700">
                                @php $key = $item->platform->key ?? ''; @endphp
                                @if ($key === 'nextengine')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 border border-amber-300">NextEngine</span>
                                @elseif ($key === 'yahoo')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Yahoo</span>
                                @elseif ($key === 'rakuten')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-700">Rakuten</span>
                                @elseif ($key === 'shopify')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Shopify</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">{{ $item->platform->name ?? '—' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $item->shop->shop_name ?? '—' }}</td>
                            <td class="px-4 py-4 text-sm text-gray-900 text-right font-bold text-lg">{{ number_format($item->stock) }}</td>
                            <td class="px-4 py-4 text-sm text-gray-900 text-right font-bold text-lg">{{ number_format($item->available_stock) }}</td>
                            <td class="px-4 py-4 text-sm text-gray-900 text-right font-bold text-lg">{{ number_format($item->reserved_stock) }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $item->variant_code ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                    </svg>
                                </div>
                                <p class="text-sm text-gray-500">在庫データがありません</p>
                                <p class="text-xs text-gray-400 mt-1">ショップ詳細ページから在庫同期を実行してください</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex-shrink-0 px-4 py-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>{{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} / {{ $items->total() }} 件</span>
            {{ $items->links() }}
        </div>
    </div>

</div>
@endsection
