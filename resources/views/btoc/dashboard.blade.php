@extends('layouts.app')

@section('content')
    <div class="max-w-[1440px]">

        <h1 class="text-3xl font-extrabold text-gray-900 mb-6">
            ダッシュボード
            <span class="block text-sm text-gray-500 font-normal mt-2">Bảng điều khiển</span>
        </h1>

        <!-- Stats -->
        <div class="grid grid-cols-5 gap-6 mb-8">
            <!-- 1 -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 hover:shadow-sm transition">
                <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center mb-6">
                    <svg viewBox="0 0 24 24" class="h-6 w-6 text-blue-700" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 21h18M4 21V7l8-4 8 4v14M9 21v-8h6v8" />
                    </svg>
                </div>
                <div class="text-4xl font-extrabold text-gray-900 mb-2">{{ $shopCount }}</div>
                <div class="text-sm font-semibold text-gray-900">登録ショップ数</div>
                <div class="text-sm text-gray-500">Số lượng shop</div>
            </div>

            <!-- 2 -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 hover:shadow-sm transition">
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mb-6">
                    <svg viewBox="0 0 24 24" class="h-6 w-6 text-green-700" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M6 6h15l-2 9H8L6 6z" />
                        <path d="M6 6H3" />
                        <path d="M8 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" />
                        <path d="M18 21a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" />
                    </svg>
                </div>
                <div class="text-4xl font-extrabold text-gray-900 mb-2">{{ $todayOrders }}</div>
                <div class="text-sm font-semibold text-gray-900">本日の注文数</div>
                <div class="text-sm text-gray-500">Số đơn hôm nay</div>
            </div>

            <!-- 3 -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 hover:shadow-sm transition">
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center mb-6">
                    <svg viewBox="0 0 24 24" class="h-6 w-6 text-amber-700" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path
                            d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                    </svg>
                </div>
                <div class="text-4xl font-extrabold text-gray-900 mb-2">{{ $unshipped }}</div>
                <div class="text-sm font-semibold text-gray-900">未発送件数</div>
                <div class="text-sm text-gray-500">Số đơn chưa gửi</div>
            </div>

            <!-- 4 -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 hover:shadow-sm transition">
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center mb-6">
                    <svg viewBox="0 0 24 24" class="h-6 w-6 text-purple-700" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M3 17l6-6 4 4 7-7" />
                        <path d="M14 7h6v6" />
                    </svg>
                </div>
                <div class="text-4xl font-extrabold text-gray-900 mb-2">{{ $todayShipped }}</div>
                <div class="text-sm font-semibold text-gray-900">本日の出庫数</div>
                <div class="text-sm text-gray-500">Số xuất hàng hôm nay</div>
            </div>

            <!-- 5 -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 hover:shadow-sm transition">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center mb-6">
                    <svg viewBox="0 0 24 24" class="h-6 w-6 text-emerald-700" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M20 6L9 17l-5-5" />
                        <path d="M20 6l-11 11" />
                    </svg>
                </div>
                <div class="text-4xl font-extrabold text-gray-900 mb-2">正常</div>
                <div class="text-sm font-semibold text-gray-900">同期ステータス</div>
                <div class="text-sm text-gray-500">Trạng thái đồng bộ</div>
            </div>
        </div>

        <!-- Shop Status -->
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-8 py-6 border-b border-gray-200">
                <h2 class="text-2xl font-extrabold text-gray-900">
                    ショップステータス
                    <span class="block text-sm text-gray-500 font-normal mt-2">Trạng thái Shop</span>
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="text-left">
                            <th class="px-8 py-4 text-sm font-semibold text-gray-900">
                                ショップ名
                                <span class="block text-xs text-gray-500 font-normal mt-1">Tên shop</span>
                            </th>
                            <th class="px-8 py-4 text-sm font-semibold text-gray-900">ShopID</th>
                            <th class="px-8 py-4 text-sm font-semibold text-gray-900">
                                NextEngine接続状態
                                <span class="block text-xs text-gray-500 font-normal mt-1">Trạng thái kết nối</span>
                            </th>
                            <th class="px-8 py-4 text-sm font-semibold text-gray-900">
                                トークン有効期限
                                <span class="block text-xs text-gray-500 font-normal mt-1">Thời hạn token</span>
                            </th>
                            <th class="px-8 py-4 text-sm font-semibold text-gray-900">
                                最終同期日時
                                <span class="block text-xs text-gray-500 font-normal mt-1">Lần đồng bộ cuối</span>
                            </th>
                            <th class="px-8 py-4 text-sm font-semibold text-gray-900">
                                エラー状態
                                <span class="block text-xs text-gray-500 font-normal mt-1">Trạng thái lỗi</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @foreach ($shops as $shop)
                            <tr class="hover:bg-gray-50">

                                <!-- Shop Name -->
                                <td class="px-8 py-5 text-sm text-gray-900">
                                    {{ $shop->shop_name }}
                                </td>

                                <!-- Shop Code -->
                                <td class="px-8 py-5 text-sm text-gray-600">
                                    {{ $shop->shop_code }}
                                </td>

                                <!-- Connection Status -->
                                <td class="px-8 py-5">
                                    @if ($shop->connection_status === 'connected')
                                        <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-600">
                                            接続中
                                        </span>
                                    @elseif($shop->connection_status === 'expired')
                                        <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-600">
                                            期限切れ
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-600">
                                            未接続
                                        </span>
                                    @endif
                                </td>

                                <!-- Token Expiry -->
                                <td class="px-8 py-5 text-sm text-gray-600">
                                    {{ $shop->token_expires_at }}
                                </td>

                                <!-- Last Sync (tạm dùng updated_at) -->
                                <td class="px-8 py-5 text-sm text-gray-600">
                                    {{ $shop->updated_at }}
                                </td>

                                <!-- Error Status -->
                                @if ($shop->connection_status === 'expired')
                                    <span class="text-red-600 font-semibold">
                                        トークン期限切れ
                                    </span>
                                @else
                                    -
                                @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
