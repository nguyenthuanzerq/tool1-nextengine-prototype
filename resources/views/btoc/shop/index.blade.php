@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] flex flex-col h-full">

    @if(session('success'))
        <div class="mb-4 flex-shrink-0 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex-shrink-0 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="mb-6 flex-shrink-0 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">ショップ管理</h1>
            <p class="text-sm text-gray-500 mt-0.5">Shop Management</p>
        </div>
        <a href="{{ route('btoc.shop.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14" />
            </svg>
            新規追加
        </a>
    </div>

    <div class="flex-1 flex flex-col min-h-0 rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">店舗 / Shop</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">プラットフォーム</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ステータス</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">最終同期</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($shops as $shop)
                    @php
                        $conn = $shop->platformConnections->first();
                        if (!$conn) {
                            $connStatus = 'disconnected';
                        } elseif ($conn->access_token && $conn->token_expires_at && $conn->token_expires_at->isPast()) {
                            $connStatus = 'expired';
                        } elseif ($conn->access_token) {
                            $connStatus = 'connected';
                        } else {
                            $connStatus = 'disconnected';
                        }
                        $connStatus = 'connected'; // TESTING - MUST DELETE
                        $lastSync = $shop->latestSyncHistory;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-4 text-sm text-gray-700">
                            <div class="font-medium text-gray-900">{{ $shop->shop_name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5 font-mono">{{ $shop->shop_code }}</div>
                        </td>

                        <td class="px-4 py-4 text-sm text-gray-700">
                            @if($shop->platform)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ $shop->platform->name }}
                                </span>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $shop->platform->auth_type }}</div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-4 text-sm text-gray-700">
                            @if($connStatus === 'connected')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                    接続済み
                                </span>
                            @elseif($connStatus === 'expired')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>
                                    期限切れ
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 inline-block"></span>
                                    未接続
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-4 text-sm text-gray-700">
                            @if($lastSync)
                                <div class="text-xs text-gray-600">{{ $lastSync->started_at?->format('Y-m-d H:i') }}</div>
                                <div class="text-xs mt-0.5">
                                    @if($lastSync->status === 'success')
                                        <span class="text-green-600">成功 / {{ $lastSync->sync_type }}</span>
                                    @elseif($lastSync->status === 'running')
                                        <span class="text-amber-500">実行中</span>
                                    @else
                                        <span class="text-red-500">失敗</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                @if($connStatus === 'connected')
                                    <form action="{{ route('btoc.sync.orders', $shop->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 4v6h6M23 20v-6h-6" /><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 0 1 3.51 15" />
                                            </svg>
                                            受注同期
                                        </button>
                                    </form>
                                    {{-- INVENTORY LEGACY BLOCK: sync button kept commented out, candidate for deletion --}}
                                    {{-- <form action="{{ route('btoc.sync.inventory', $shop->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium bg-purple-100 text-purple-700 hover:bg-purple-200 transition">
                                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M1 4v6h6M23 20v-6h-6" /><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 0 1 3.51 15" />
                                            </svg>
                                            在庫同期
                                        </button>
                                    </form> --}}
                                    {{-- END INVENTORY LEGACY BLOCK --}}
                                @elseif($connStatus === 'expired')
                                    <a href="{{ route('btoc.shop.show', $shop->id) }}?tab=connection"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium bg-amber-100 text-amber-700 hover:bg-amber-200 transition">
                                        再接続
                                    </a>
                                @else
                                    <a href="{{ route('btoc.shop.show', $shop->id) }}?tab=connection"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                        設定
                                    </a>
                                @endif
                                <a href="{{ route('btoc.shop.show', $shop->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                    表示
                                </a>
                                <a href="{{ route('btoc.shop.edit', $shop->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium bg-blue-100 text-blue-700 hover:bg-blue-200 transition">
                                    編集
                                </a>
                                <form action="{{ route('btoc.shop.destroy', $shop->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('このショップを削除しますか？ / Delete this shop?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium bg-white border border-red-200 text-red-600 hover:bg-red-50 transition">
                                        削除
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center">
                            <p class="text-sm text-gray-500">ショップがありません</p>
                            <p class="text-xs text-gray-400 mt-1">No shops yet.</p>
                            <a href="{{ route('btoc.shop.create') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">追加する</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex-shrink-0 px-4 py-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>{{ $shops->firstItem() ?? 0 }}–{{ $shops->lastItem() ?? 0 }} / {{ $shops->total() }} 件</span>
            {{ $shops->links() }}
        </div>
    </div>
</div>
@endsection
