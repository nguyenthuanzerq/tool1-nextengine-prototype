@extends('layouts.app')

@section('content')
    <div class="p-6 max-w-[1440px] mx-auto ">
        <a href="{{ route('btoc.shop.index') }}"
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

        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-6">
                <h1 class="text-2xl font-semibold text-gray-900 mb-6">
                    {{ $isCreate ? '新規ショップ追加 (Thêm Shop Mới)' : 'ショップ編集 (Chỉnh sửa Shop)' }}
                </h1>
                <form method="POST"
                    action="{{ $isCreate ? route('btoc.shop.store') : route('btoc.shop.update', $shop->id) }}">
                    @csrf
                    @if (!$isCreate)
                        @method('PUT')
                    @endif

                    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm max-w-xl space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ショップコード (Mã Shop) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="shop_code" value="{{ old('shop_code', $shop->shop_code) }}"
                                {{-- {{ !$isCreate ? 'readonly' : '' }} --}}
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg {{ !$isCreate ? 'bg-gray-100' : '' }}"
                                required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ショップ名 (Tên Shop) <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="shop_name" value="{{ old('shop_name', $shop->shop_name) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('btoc.shop.index') }}"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">キャンセル
                            (Hủy)</a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">保存
                            (Lưu)</button>
                    </div>
                </form>
            </div>
            @if (!$isCreate)
                <div class="col-span-6">
                    <h1 class="text-2xl font-semibold text-gray-900 mb-6">
                        NEXT ENGINE API 認証情報 (Thông tin xác thực API NEXT ENGINE)
                    </h1>
                    <form method="POST" action="{{ route('btoc.shop.nextengine_connection', $shop->id) }}">
                        @csrf
                        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm max-w-xl space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client ID <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="client_id" value="{{ old('client_id', $shop->client_id) }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret <span
                                        class="text-red-500">*</span></label>
                                <input type="text" name="client_secret" value=""
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                            </div>


                        </div>
                        <div class="flex gap-3">
                            <a href="{{ route('btoc.shop.index') }}"
                                class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">キャンセル
                                (Hủy)</a>
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">保存
                                (Lưu)</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
