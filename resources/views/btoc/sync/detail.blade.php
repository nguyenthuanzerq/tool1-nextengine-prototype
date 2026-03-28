@extends('layouts.app')

@section('content')
<div class="max-w-[1440px]">

    <a href="{{ route('btoc.sync.history') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 mb-5 transition">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
        同期履歴一覧に戻る
    </a>

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">同期詳細</h1>
            <p class="text-sm text-gray-500 mt-0.5">Sync Detail</p>
        </div>

        <div class="flex gap-3">
            @if($history->shop_id)
                <a href="{{ route('btoc.shop.show', $history->shop_id) }}?tab=connection"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                    </svg>
                    接続設定
                </a>
            @endif
            @if($history->status === 'failed' && $history->shop_id)
            <form method="POST"
                  action="{{ route('btoc.sync.' . ($history->sync_type === 'inventory' ? 'inventory' : 'orders'), $history->shop_id) }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 4v6h6M23 20v-6h-6" /><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 0 1 3.51 15" />
                    </svg>
                    再同期
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Summary card --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-5 max-w-2xl">
        <dl class="divide-y divide-gray-100">
            <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">Sync Code</dt>
                <dd class="text-sm font-mono text-gray-900 col-span-2">{{ $history->sync_code }}</dd>
            </div>
            <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">ショップ</dt>
                <dd class="text-sm text-gray-900 col-span-2">
                    {{ $history->shop->shop_name ?? $history->shop_name ?? '—' }}
                </dd>
            </div>
            <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">プラットフォーム</dt>
                <dd class="text-sm text-gray-900 col-span-2">
                    {{ $history->platform->name ?? '—' }}
                </dd>
            </div>
            <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">種別 / Type</dt>
                <dd class="text-sm col-span-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                        {{ $history->sync_type === 'orders' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                        {{ $history->sync_type === 'orders' ? '注文 / Orders' : '在庫 / Inventory' }}
                    </span>
                </dd>
            </div>
            <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">状態 / Status</dt>
                <dd class="text-sm col-span-2">
                    @if($history->status === 'success')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">成功 / Success</span>
                    @elseif($history->status === 'running')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">実行中 / Running</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">失敗 / Failed</span>
                    @endif
                </dd>
            </div>
            <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">開始 / Started</dt>
                <dd class="text-sm text-gray-900 col-span-2">
                    {{ $history->started_at?->format('Y-m-d H:i:s') ?? '—' }}
                </dd>
            </div>
            <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">終了 / Ended</dt>
                <dd class="text-sm text-gray-900 col-span-2">
                    {{ $history->ended_at?->format('Y-m-d H:i:s') ?? '—' }}
                </dd>
            </div>
            @if($history->ended_at && $history->started_at)
            <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">所要時間</dt>
                <dd class="text-sm text-gray-900 col-span-2">
                    {{ $history->started_at->diffInSeconds($history->ended_at) }}s
                </dd>
            </div>
            @endif
            @if(isset($history->meta['synced_count']))
            <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-gray-500">件数 / Records</dt>
                <dd class="text-sm text-gray-900 col-span-2">
                    {{ $history->meta['synced_count'] }}
                </dd>
            </div>
            @endif
            @if($history->error_message)
            <div class="py-3 grid grid-cols-3 gap-4">
                <dt class="text-sm font-medium text-red-500">エラー / Error</dt>
                <dd class="text-sm text-red-700 col-span-2 break-all">
                    {{ $history->error_message }}
                </dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- Meta JSON --}}
    @if($history->meta && count($history->meta) > 1)
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-2xl">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Meta</h2>
        <pre class="bg-gray-50 rounded-lg p-4 text-xs text-gray-700 overflow-x-auto font-mono border border-gray-200">{{ json_encode($history->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    @endif
</div>
@endsection
