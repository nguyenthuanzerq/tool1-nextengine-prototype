@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">
    <a href="{{ route('btoc.orders.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6 transition">
        ← 戻る (Quay lại)
    </a>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            注文詳細 <span class="block text-sm text-gray-500 font-normal mt-1">Chi tiết Đơn hàng</span>
        </h1>
        {{-- <a href="{{ route('btoc.orders.edit', $order->id) }}" class="px-4 py-2 bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 rounded-lg text-sm transition">
            編集する (Chỉnh sửa)
        </a> --}}
    </div>

    <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm max-w-4xl grid grid-cols-1 md:grid-cols-2 gap-x-8">
        {{-- Cột 1: Thông tin đơn hàng --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Thông tin Đơn hàng</h3>
            <dl class="divide-y divide-gray-100">
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Mã Đơn hàng</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->receipt_receipt_id }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Cửa hàng</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->shop->shop_name ?? '-' }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Ngày Đặt hàng</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->receive_order_date }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Tổng Tiền</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0 font-semibold text-red-600">¥{{ number_format($order->receive_order_total_amount) }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Trạng Thái</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->status }}</dd>
                </div>
            </dl>
        </div>

        {{-- Cột 2: Thông tin Khách hàng & Giao hàng --}}
        <div>
            <h3 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Thông tin Khách hàng & Giao hàng</h3>
            <dl class="divide-y divide-gray-100">
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">ID Người mua</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->purchaser_id ?? '-' }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Tên Khách hàng</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->purchaser_name }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Số điện thoại</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->purchaser_phone ?? '-' }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Email</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->purchaser_email ?? '-' }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Địa chỉ</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->shipping_address ?? '-' }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Hãng vận chuyển</dt>
                    <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->carrier_name ?? '-' }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-900">Mã Vận đơn</dt>
                    <dd class="mt-1 text-sm text-blue-600 sm:col-span-2 sm:mt-0">{{ $order->shipping_delivery_tracking_number ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection