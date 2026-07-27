@extends('layouts.app')

@section('content')
    <div class="max-w-[1440px]">

        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900">ダッシュボード</h1>
            <p class="text-sm text-gray-500 mt-0.5">Dashboard</p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
            <!-- 1: Shops -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:shadow transition">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-blue-700" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 21h18M4 21V7l8-4 8 4v14M9 21v-8h6v8" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">{{ $shopCount }}</div>
                <div class="text-sm font-semibold text-gray-800">登録ショップ数</div>
                <div class="text-xs text-gray-400 mt-0.5">Registered Shops</div>
            </div>

            <!-- 2: Today's Orders -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:shadow transition">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center mb-4">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-green-700" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2" />
                        <rect x="9" y="3" width="6" height="4" rx="1" />
                        <path d="M9 12h6M9 16h4" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">{{ $todayOrders }}</div>
                <div class="text-sm font-semibold text-gray-800">本日の注文数</div>
                <div class="text-xs text-gray-400 mt-0.5">Today's Orders</div>
            </div>

            <!-- 3: Unshipped -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:shadow transition">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center mb-4">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-amber-700" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                        <path d="M3.27 6.96 12 12.01l8.73-5.05M12 22.08V12" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">{{ $unshipped }}</div>
                <div class="text-sm font-semibold text-gray-800">未発送件数</div>
                <div class="text-xs text-gray-400 mt-0.5">Unshipped Orders</div>
            </div>

            <!-- 4: Today's Shipments -->
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:shadow transition">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center mb-4">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-purple-700" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 17l6-6 4 4 7-7" />
                        <path d="M14 7h6v6" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">{{ $todayShipped }}</div>
                <div class="text-sm font-semibold text-gray-800">本日の出庫数</div>
                <div class="text-xs text-gray-400 mt-0.5">Today's Shipments</div>
            </div>

            <!-- 5: Last Sync -->
            @php
                $latestSync = \App\Models\SyncHistory::latest('started_at')->first();
            @endphp
            <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm hover:shadow transition">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center mb-4">
                    <svg viewBox="0 0 24 24" class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M12 8v4l3 3" />
                        <path d="M3.05 11a9 9 0 1 0 .5-3" />
                        <path d="M3 4v4h4" />
                    </svg>
                </div>
                @if($latestSync)
                    <div class="text-sm font-bold text-gray-900 mb-1">
                        @if($latestSync->status === 'success')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">成功</span>
                        @elseif($latestSync->status === 'running')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">実行中</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">失敗</span>
                        @endif
                    </div>
                    <div class="text-xs text-gray-500 leading-tight">{{ $latestSync->started_at?->format('Y-m-d H:i') ?? '—' }}</div>
                @else
                    <div class="text-sm font-bold text-gray-400">—</div>
                @endif
                <div class="text-sm font-semibold text-gray-800 mt-1">最終同期</div>
                <div class="text-xs text-gray-400 mt-0.5">Last Sync</div>
            </div>
        </div>

        <!-- Shop Status -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-bold text-gray-900">ショップステータス</h2>
                <p class="text-xs text-gray-400 mt-0.5">Shop Status</p>
            </div>

            @if($shops->isEmpty())
                <div class="px-6 py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                        <svg viewBox="0 0 24 24" class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M3 21h18M4 21V7l8-4 8 4v14M9 21v-8h6v8" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">ショップがありません</p>
                    <p class="text-xs text-gray-400 mt-0.5">No shops registered yet</p>
                    <a href="{{ route('btoc.shop.create') }}" class="inline-flex items-center gap-1.5 mt-4 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                        ショップを追加
                    </a>
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ショップ名
                                <span class="block text-gray-400 font-normal normal-case">Shop Name</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shop ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                接続状態
                                <span class="block text-gray-400 font-normal normal-case">Connection Status</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                トークン有効期限
                                <span class="block text-gray-400 font-normal normal-case">Token Expiry</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                最終同期日時
                                <span class="block text-gray-400 font-normal normal-case">Last Synced</span>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                エラー状態
                                <span class="block text-gray-400 font-normal normal-case">Error Status</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        @foreach ($shops as $shop)
                        @php
                            $conn = $shop->platformConnections->first();
                            $connStatus = 'disconnected';
                            if ($conn) {
                                $platformKey = $shop->platform->key ?? '';
                                if (in_array($platformKey, ['rakuten'])) {
                                    // API Key based platforms
                                    if ($conn->client_id && $conn->client_secret) {
                                        $connStatus = 'connected';
                                    }
                                } else {
                                    // OAuth based platforms
                                    if ($conn->access_token && $conn->token_expires_at && $conn->token_expires_at->isPast()) {
                                        $connStatus = 'expired';
                                    } elseif ($conn->access_token) {
                                        $connStatus = 'connected';
                                    }
                                }
                            }
                            $lastSync = $shop->latestSyncHistory;
                        @endphp
                            <tr class="hover:bg-gray-50 transition">

                                <td class="px-4 py-4 text-sm text-gray-900 font-medium">
                                    <a href="{{ route('btoc.shop.show', $shop->id) }}" class="hover:text-blue-600 transition">
                                        {{ $shop->shop_name }}
                                    </a>
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-500 font-mono">
                                    {{ $shop->shop_code }}
                                </td>

                                <td class="px-4 py-4">
                                    @if ($connStatus === 'connected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                                            接続済み
                                        </span>
                                    @elseif($connStatus === 'expired')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>
                                            期限切れ
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300 inline-block"></span>
                                            未接続
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-500">
                                    {{ $conn?->token_expires_at?->format('Y-m-d H:i') ?? '—' }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-500">
                                    {{ $lastSync?->started_at?->format('Y-m-d H:i') ?? '—' }}
                                </td>

                                <td class="px-4 py-4 text-sm">
                                    @if ($connStatus === 'expired')
                                        <span class="text-amber-600 font-medium text-xs">トークン期限切れ</span>
                                    @elseif($connStatus === 'disconnected')
                                        <span class="text-gray-400 text-xs">—</span>
                                    @else
                                        <span class="text-green-600 text-xs">正常</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>
@endsection
