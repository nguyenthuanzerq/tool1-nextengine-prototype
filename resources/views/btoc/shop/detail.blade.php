@extends('layouts.app')

@section('content')
@php
    $activeTab = request('tab', 'connection');
    $platform  = $shop->platform;
    $authType  = $platform?->auth_type ?? 'oauth2';

    // Derive connection status
    if (!$connection) {
        $connStatus = 'disconnected';
    } elseif ($connection->access_token && $connection->token_expires_at && $connection->token_expires_at->isPast()) {
        $connStatus = 'expired';
    } elseif ($connection->access_token) {
        $connStatus = 'connected';
    } else {
        $connStatus = 'disconnected';
    }

    $statusBadge = match($connStatus) {
        'connected'    => ['bg-green-100 text-green-700',  '接続済み'],
        'expired'      => ['bg-amber-100  text-amber-700', '期限切れ'],
        default        => ['bg-gray-100  text-gray-500',   '未接続'],
    };
@endphp

<div class="max-w-[1440px]">
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Breadcrumb --}}
    <a href="{{ route('btoc.shop.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 mb-4 transition">
        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M15 18l-6-6 6-6" />
        </svg>
        ショップ一覧
    </a>

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $shop->shop_name }}</h1>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-gray-400 font-mono">{{ $shop->shop_code }}</span>
                @if($platform)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                        {{ $platform->name }}
                    </span>
                @endif
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge[0] }}">
                    {{ $statusBadge[1] }}
                </span>
            </div>
        </div>
        <a href="{{ route('btoc.shop.edit', $shop->id) }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
            </svg>
            ショップ編集
        </a>
    </div>

    {{-- Tab navigation --}}
    <div class="border-b border-gray-200 mb-6">
        <nav class="flex gap-0">
            @php
                $tabs = [
                    'connection' => ['label' => '接続設定', 'sub' => 'Connection'],
                    'sync'       => ['label' => '同期実行', 'sub' => 'Sync'],
                    'history'    => ['label' => '同期履歴', 'sub' => 'History'],
                ];
            @endphp
            @foreach($tabs as $tab => $info)
                <a href="{{ route('btoc.shop.show', $shop->id) }}?tab={{ $tab }}"
                   class="inline-flex flex-col items-center px-5 py-3 text-sm font-medium border-b-2 transition
                       {{ $activeTab === $tab
                           ? 'border-blue-600 text-blue-600'
                           : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span>{{ $info['label'] }}</span>
                    <span class="text-xs {{ $activeTab === $tab ? 'text-blue-400' : 'text-gray-400' }}">{{ $info['sub'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>

    {{-- ═══════════════════════ TAB: CONNECTION ═══════════════════════ --}}
    @if($activeTab === 'connection')
    <div class="max-w-xl">
        <div class="mb-4">
            <h2 class="text-base font-semibold text-gray-800">{{ $platform?->name ?? 'Platform' }} Credentials</h2>
            <p class="text-xs text-gray-400 mt-0.5">API認証情報 / Platform credentials</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-600">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('btoc.shop.nextengine_connection', $shop->id) }}">
            @csrf
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">

                {{-- OAuth2 fields --}}
                @if($authType !== 'api_key')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client ID <span class="text-red-500">*</span></label>
                    <input type="text" name="client_id"
                           value="{{ old('client_id', $connection?->client_id) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret <span class="text-red-500">*</span></label>
                    <input type="text" name="client_secret"
                           value="{{ old('client_secret', $connection?->client_secret) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Access Token</label>
                    <input type="text" value="{{ $connection?->access_token ? '設定済み (set)' : '—' }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-500 bg-gray-50" readonly>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Refresh Token</label>
                    <input type="text" value="{{ $connection?->refresh_token ? '設定済み (set)' : '—' }}"
                           class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-500 bg-gray-50" readonly>
                </div>
                @if($connection?->token_expires_at)
                <div class="rounded-lg px-3 py-2 text-xs {{ $connection->token_expires_at->isPast() ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-700' }}">
                    Token expires: {{ $connection->token_expires_at->format('Y-m-d H:i') }}
                    &mdash; {{ $connection->token_expires_at->isPast() ? '期限切れ (Expired)' : '有効 (Valid)' }}
                </div>
                @endif

                {{-- API Key fields --}}
                @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">API Key (Service Secret) <span class="text-red-500">*</span></label>
                    <input type="text" name="client_id"
                           value="{{ old('client_id', $connection?->client_id) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">License Key <span class="text-red-500">*</span></label>
                    <input type="text" name="client_secret"
                           value="{{ old('client_secret', $connection?->client_secret) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                @endif
            </div>

            <div class="flex flex-wrap gap-3 mt-4">
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>
                    認証情報を保存
                </button>

                @if($authType !== 'api_key' && $connection?->client_id)
                    <a href="{{ route('nextengine.connect', ['id' => $shop->id]) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-green-600 hover:bg-green-700 text-white transition">
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                        OAuth 連携 / Connect
                    </a>
                @endif
            </div>
        </form>
    </div>
    @endif

    {{-- ═══════════════════════ TAB: SYNC ═══════════════════════ --}}
    @if($activeTab === 'sync')
    <div class="max-w-xl space-y-5">

        @if($connStatus !== 'connected')
            <div class="bg-amber-50 border border-amber-200 text-amber-700 rounded-lg px-4 py-3 text-sm">
                未接続のショップです。まず
                <a href="{{ route('btoc.shop.show', $shop->id) }}?tab=connection"
                   class="underline font-medium">接続設定タブ</a>
                で認証情報を設定してください。
            </div>
        @endif

        {{-- Sync Orders --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">受注同期</h2>
            <p class="text-xs text-gray-400 mb-4">Sync Orders</p>
            <form method="POST" action="{{ route('btoc.sync.orders', $shop->id) }}">
                @csrf
                <button type="submit" {{ $connStatus !== 'connected' ? 'disabled' : '' }}
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white transition">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 4v6h6M23 20v-6h-6" /><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 0 1 3.51 15" />
                    </svg>
                    受注同期を開始
                </button>
            </form>
        </div>

        {{-- Sync Inventory --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">在庫同期</h2>
            <p class="text-xs text-gray-400 mb-4">Sync Inventory</p>
            <form method="POST" action="{{ route('btoc.sync.inventory', $shop->id) }}">
                @csrf
                <button type="submit" {{ $connStatus !== 'connected' ? 'disabled' : '' }}
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-purple-100 text-purple-700 hover:bg-purple-200 disabled:opacity-50 disabled:cursor-not-allowed transition">
                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 4v6h6M23 20v-6h-6" /><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 0 1 3.51 15" />
                    </svg>
                    在庫同期を開始
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════ TAB: HISTORY ═══════════════════════ --}}
    @if($activeTab === 'history')
    <div>
        <div class="mb-4">
            <h2 class="text-base font-semibold text-gray-800">同期履歴</h2>
            <p class="text-xs text-gray-400 mt-0.5">Sync History — {{ $shop->shop_name }}</p>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sync Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">種別</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">開始日時</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">所要時間</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状態</th>
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
                        <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-400">
                            同期履歴がありません。
                            <a href="{{ route('btoc.shop.show', $shop->id) }}?tab=sync"
                               class="text-blue-600 hover:underline">同期を実行する</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
