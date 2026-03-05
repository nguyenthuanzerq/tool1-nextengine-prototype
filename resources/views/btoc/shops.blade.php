@extends('layouts.app')

@section('content')

<div class="p-6 max-w-[1440px] mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            ショップ管理
            <span class="block text-sm text-gray-500 font-normal mt-1">
                Quản lý Shop
            </span>
        </h1>

        <a href="{{ route('btoc.shop.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">

            <div class="flex flex-col items-start">
                <span>新規ショップ登録</span>
                <span class="text-xs opacity-90">(Thêm shop mới)</span>
            </div>
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                        ショップ名
                        <span class="block text-gray-500 font-normal">
                            Tên shop
                        </span>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                        ShopID
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                        接続状態
                        <span class="block text-gray-500 font-normal">
                            Trạng thái kết nối
                        </span>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                        最終同期
                        <span class="block text-gray-500 font-normal">
                            Lần đồng bộ cuối
                        </span>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                        操作
                        <span class="block text-gray-500 font-normal">
                            Thao tác
                        </span>
                    </th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                @foreach($shops as $shop)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $shop->shop_name }}
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $shop->shop_id }}
                        </td>

                        <td class="px-6 py-4">
                            @if($shop->connection_status === 'connected')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    ● 接続中
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    ● トークン期限切れ
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $shop->last_sync_at }}
                        </td>

                        <td class="px-6 py-4">
                            <a href="{{ route('btoc.shop.edit',$shop->id) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                編集
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
    </div>

</div>

@endsection