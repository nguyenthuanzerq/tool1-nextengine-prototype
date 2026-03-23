@extends('layouts.app')

@section('content')
    <div class="p-6 max-w-[1440px] mx-auto">
        <a href="{{ route('btoc.orders.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6 transition">
            ← 戻る (Quay lại danh sách)
        </a>

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">
                注文詳細 <span class="block text-sm text-gray-500 font-normal mt-1">Chi tiết Đơn hàng</span>
            </h1>
        </div>

        <div
            class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-x-12">

            {{-- Cột 1: Thông tin đơn hàng --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">
                    注文情報 <span class="text-sm text-gray-500 font-normal">(Thông tin Đơn hàng)</span>
                </h3>
                <dl class="divide-y divide-gray-100">
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            注文ID <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Mã Đơn hàng</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-900 font-medium sm:col-span-2 sm:mt-0">
                            {{ $order->receive_order_id ?? '-' }}</dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            店舗 <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Cửa hàng</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">{{ $order->shop->shop_name ?? '-' }}
                        </dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            注文日時 <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Ngày Đặt hàng</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                            {{ $order->receive_order_date ? $order->receive_order_date->format('Y-m-d H:i:s') : '-' }}</dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            合計金額 <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Tổng Tiền</span>
                        </dt>
                        <dd class="mt-1 text-sm sm:col-span-2 sm:mt-0 font-semibold text-red-600">
                            ¥{{ number_format(($order->receive_order_goods_amount ?? 0) + ($order->receive_order_delivery_fee_amount ?? 0)) }}
                        </dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            ステータス <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Trạng Thái</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                            <span
                                class="px-2 py-1 bg-gray-100 text-gray-700 border border-gray-200 rounded text-xs font-medium">{{ $order->receive_order_status_label ?? '-' }}</span>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Cột 2: Thông tin Khách hàng & Giao hàng --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-200 pb-2 mb-4">
                    顧客・配送情報 <span class="text-sm text-gray-500 font-normal">(Thông tin Khách hàng & Giao hàng)</span>
                </h3>
                <dl class="divide-y divide-gray-100">
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            購入者ID <span class="block text-[11px] text-gray-500 font-normal mt-0.5">ID Người mua</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                            {{ $order->receive_order_purchaser_id ?? '-' }}</dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            顧客名 <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Tên Khách hàng</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                            {{ $order->receive_order_creator_name ?? '-' }}</dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            電話番号 <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Số điện thoại</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                            {{ $order->receive_order_purchaser_tel ?? '-' }}</dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            メールアドレス <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Email</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                            {{ $order->receive_order_purchaser_mail_address ?? '-' }}</dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-start">
                        <dt class="text-sm font-medium text-gray-900">
                            住所 <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Địa chỉ</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0 leading-relaxed">
                            {{ trim(($order->receive_order_purchaser_address1 ?? '') . ' ' . ($order->receive_order_purchaser_address2 ?? '')) ?: '-' }}
                        </dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            配送会社 <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Hãng vận chuyển</span>
                        </dt>
                        <dd class="mt-1 text-sm text-gray-700 sm:col-span-2 sm:mt-0">
                            {{ $order->receive_order_delivery_method_name ?? '-' }}</dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4 items-center">
                        <dt class="text-sm font-medium text-gray-900">
                            追跡番号 <span class="block text-[11px] text-gray-500 font-normal mt-0.5">Mã Vận đơn</span>
                        </dt>
                        <dd class="mt-1 text-sm text-blue-600 font-medium sm:col-span-2 sm:mt-0">
                            {{ $order->tracking_number ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection
