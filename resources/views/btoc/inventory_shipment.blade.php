@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">

    <h1 class="text-2xl font-semibold text-gray-900 mb-6">
        在庫・出庫
        <span class="block text-sm text-gray-500 font-normal mt-1">
            Tồn kho & Xuất hàng
        </span>
    </h1>

    {{-- Inventory Section --}}
    <div class="bg-white rounded-lg border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">
                在庫一覧
                <span class="block text-sm text-gray-500 font-normal mt-0.5">
                    Danh sách tồn kho
                </span>
            </h2>

            <form method="POST" action="{{ route('btoc.inventory.refresh') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    リアルタイム更新
                    <span class="text-xs opacity-90">(Cập nhật thời gian thực)</span>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            {{-- ✅ chỉ thêm table-fixed --}}
            <table class="w-full table-fixed">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 w-[180px]">
                            ショップ名
                            <span class="block text-gray-500 font-normal">Tên shop</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            商品コード
                            <span class="block text-gray-500 font-normal">Mã sản phẩm</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            商品名
                            <span class="block text-gray-500 font-normal">Tên sản phẩm</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            在庫数
                            <span class="block text-gray-500 font-normal">Số lượng tồn</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            出庫可能数
                            <span class="block text-gray-500 font-normal">Số có thể xuất</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            最終更新日時
                            <span class="block text-gray-500 font-normal">Cập nhật cuối</span>
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @foreach($inventoryData as $item)
                    <tr>
                        {{-- ✅ chỉ thêm width + nowrap --}}
                        <td class="px-6 py-4 text-sm text-gray-900 w-[180px] whitespace-nowrap">
                            {{ $item->shop_name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->product_code }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->product_name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->stock }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->available_stock }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $item->last_updated }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Shipment Section --}}
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">
                出庫状況
                <span class="block text-sm text-gray-500 font-normal mt-0.5">
                    Tình trạng xuất hàng
                </span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            注文ID
                            <span class="block text-gray-500 font-normal">ID đơn hàng</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            出庫状態
                            <span class="block text-gray-500 font-normal">Trạng thái xuất</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            出庫日
                            <span class="block text-gray-500 font-normal">Ngày xuất</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            運送会社
                            <span class="block text-gray-500 font-normal">Công ty vận chuyển</span>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            送り状番号
                            <span class="block text-gray-500 font-normal">Số tracking</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($shipmentData as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item['orderId'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium
                                    {{ $item['shipmentStatus'] === '出庫済'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $item['shipmentStatus'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item['shipmentDate'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item['carrier'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $item['trackingNumber'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection