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

        <form method="POST" action="{{ route('btoc.orders.update', $order->id) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm max-w-2xl space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">お問い合わせ番号 (Mã vận đơn)</label>
                        <input type="text" name="tracking_number"
                            value="{{ old('tracking_number', $order->tracking_number) }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

            </div>

            <div class="flex gap-3">
                <a href="{{ route('btoc.orders.index') }}"
                    class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">キャンセル (Hủy)</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">保存
                    (Lưu)</button>
            </div>
        </form>
    </div>
@endsection
