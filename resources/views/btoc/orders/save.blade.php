@extends('layouts.app')

@section('content')
    <div class="p-6 max-w-[1440px] mx-auto">
        <a href="{{ route('btoc.orders.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6 transition">
            ← 戻る (Quay lại)
        </a>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-600">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h1 class="text-2xl font-semibold text-gray-900 mb-6">
            {{ $isCreate ? '新規注文追加 (Thêm Đơn hàng Mới)' : '注文編集 (Chỉnh sửa Đơn hàng)' }}
        </h1>

        <form method="POST" action="{{ $isCreate ? route('btoc.orders.store') : route('btoc.orders.update', $order->id) }}">
            @csrf
            @if (!$isCreate)
                @method('PUT')
            @endif

            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm max-w-2xl space-y-4">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">店舗 (Shop) <span class="text-red-500">*</span></label>
                        <select name="shop_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="">選択してください (Vui lòng chọn)</option>
                            @foreach ($shops as $shop)
                                <option value="{{ $shop->id }}" {{ old('shop_id', $order->shop_id) == $shop->id ? 'selected' : '' }}>
                                    {{ $shop->shop_code }} - {{ $shop->shop_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">受注番号 (Mã đơn hàng) <span class="text-red-500">*</span></label>
                        <input type="text" name="receipt_receipt_id" value="{{ old('receipt_receipt_id', $order->receipt_receipt_id) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">受注日 (Ngày đặt) <span class="text-red-500">*</span></label>
                        <input type="date" name="receive_order_date" value="{{ old('receive_order_date', $order->receive_order_date ? $order->receive_order_date->format('Y-m-d') : '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">合計金額 (Tổng tiền) <span class="text-red-500">*</span></label>
                        <input type="number" name="receive_order_total_amount" value="{{ old('receive_order_total_amount', $order->receive_order_total_amount) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <hr class="my-4 border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Thông tin Khách hàng (購入者情報)</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">注文者 ID (ID Người mua)</label>
                        <input type="text" name="purchaser_id" value="{{ old('purchaser_id', $order->purchaser_id) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">購入者名 (Tên người mua) <span class="text-red-500">*</span></label>
                        <input type="text" name="purchaser_name" value="{{ old('purchaser_name', $order->purchaser_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">電話番号 (Số điện thoại)</label>
                        <input type="text" name="purchaser_phone" value="{{ old('purchaser_phone', $order->purchaser_phone) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">メールアドレス (Email)</label>
                        <input type="email" name="purchaser_email" value="{{ old('purchaser_email', $order->purchaser_email) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">配送先住所 (Địa chỉ nhận hàng)</label>
                    <textarea name="shipping_address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg">{{ old('shipping_address', $order->shipping_address) }}</textarea>
                </div>

                <hr class="my-4 border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Thông tin Giao hàng (配送情報)</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">配送会社 (Công ty vận chuyển)</label>
                        <input type="text" name="carrier_name" value="{{ old('carrier_name', $order->carrier_name) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                    @if (!$isCreate)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">お問い合わせ番号 (Mã vận đơn)</label>
                        <input type="text" name="shipping_delivery_tracking_number" value="{{ old('shipping_delivery_tracking_number', $order->shipping_delivery_tracking_number) }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">ステータス (Trạng thái) <span class="text-red-500">*</span></label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="pending" {{ old('status', $order->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ old('status', $order->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $order->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>

            </div>

            <div class="flex gap-3">
                <a href="{{ route('btoc.orders.index') }}"
                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">キャンセル (Hủy)</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">保存 (Lưu)</button>
            </div>
        </form>
    </div>
@endsection