@extends('layouts.app')

@section('content')
<div class="max-w-[1440px] flex flex-col h-full">

    <div class="mb-6 flex-shrink-0">
        <h1 class="text-xl font-bold text-gray-900">同期履歴</h1>
        <p class="text-sm text-gray-500 mt-0.5">Sync History</p>
    </div>

    @if(session('success'))
        <div class="mb-4 flex-shrink-0 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 flex-shrink-0 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="flex-1 flex flex-col min-h-0 bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="flex-1 overflow-auto min-h-0">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sync Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ショップ</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">プラットフォーム</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">種別 / Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">開始 / Started</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">所要時間</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状態 / Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">件数</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($histories as $h)
                    @php
                        $duration = $h->ended_at && $h->started_at
                            ? $h->started_at->diffInSeconds($h->ended_at) . 's'
                            : '—';
                        $count = $h->meta['synced_count'] ?? '—';
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-4 text-xs font-mono text-gray-500">{{ $h->sync_code }}</td>
                        <td class="px-4 py-4 text-sm text-gray-700">{{ $h->shop->shop_name ?? $h->shop_name ?? '—' }}</td>
                        <td class="px-4 py-4 text-sm text-gray-500">{{ $h->platform->name ?? '—' }}</td>
                        <td class="px-4 py-4 text-sm text-gray-700">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $h->sync_type === 'orders' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $h->sync_type === 'orders' ? '注文' : '在庫' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">{{ $h->started_at?->format('Y-m-d H:i:s') }}</td>
                        <td class="px-4 py-4 text-sm text-gray-500">{{ $duration }}</td>
                        <td class="px-4 py-4 text-sm text-gray-700">
                            @if($h->status === 'success')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700">成功</span>
                            @elseif($h->status === 'running')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">実行中</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">失敗</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-700">{{ $count }}</td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('btoc.sync.history.detail', $h->id) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md text-xs font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                詳細
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-sm text-gray-400">
                            同期履歴がありません。まずショップ詳細から同期を実行してください。
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex-shrink-0 px-4 py-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>{{ $histories->firstItem() ?? 0 }}–{{ $histories->lastItem() ?? 0 }} / {{ $histories->total() }} 件</span>
            {{ $histories->links() }}
        </div>
    </div>
</div>
@endsection
