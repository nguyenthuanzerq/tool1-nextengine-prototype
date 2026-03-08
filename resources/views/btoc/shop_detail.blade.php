@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">

    <a href="{{ route('btoc.shops') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6 transition">
        ← 戻る (Quay lại)
    </a>

    {{-- Hiển thị thông báo chung --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">{{ session('error') }}</div>
    @endif

    {{-- Hiển thị lỗi Validation --}}
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <ul class="list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            {{ $isCreate ? '新規ショップ追加 ' : 'ショップ詳細・編集 ' }}
        </h1>

        {{-- Các nút API chỉ hiện khi đang ở chế độ Edit (Shop đã được lưu) --}}
        @if(!$isCreate)
            <div class="flex gap-3">
                {{-- Nút Test Connection dùng Form ẩn --}}
                <form id="test-conn-form" action="{{ route('btoc.shop.testConnection', $shop->id) }}" method="POST" class="hidden">
                    @csrf
                </form>
                <button type="button" onclick="document.getElementById('test-conn-form').submit();" class="px-4 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 rounded-lg text-sm transition">
                    接続テスト (Test connection)
                </button>

                {{-- Nút Re-authorize chuyển hướng --}}
                <a href="{{ route('btoc.shop.reAuthorize', $shop->id) }}" class="px-4 py-2 bg-[#00B900] hover:bg-[#00A000] text-white rounded-lg text-sm transition shadow-sm">
                    再認証 (Re-authorize)
                </a>
            </div>
        @endif
    </div>

    {{-- Bắt đầu Form chính cho Store / Update --}}
    <form method="POST" action="{{ $isCreate ? route('btoc.shop.store') : route('btoc.shop.update', $shop->id) }}">
        @csrf
        @if(!$isCreate)
            @method('PUT')
        @endif

        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm">
            <h2 class="text-lg font-semibold mb-4 border-b pb-2">基本情報 </h2>
            <div class="max-w-xl space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        ショップコード (Mã Shop) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="shop_code" value="{{ old('shop_code', $shop->shop_code) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" 
                           placeholder="VD: SHOP-01" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        ショップ名 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="shop_name" value="{{ old('shop_name', $shop->shop_name) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" >
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 shadow-sm">
            <h2 class="text-lg font-semibold mb-4 border-b pb-2">
                NextEngine API設定
                <span class="block text-sm text-gray-500 font-normal mt-1">Cấu hình kết nối API</span>
            </h2>

            <div class="max-w-xl space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Client ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="client_id" value="{{ old('client_id', $shop->client_id) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono text-sm" >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Client Secret
                    </label>
                    <input type="password" name="client_secret" value="{{ old('client_secret', $shop->client_secret) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono text-sm">
                    <p class="text-xs text-gray-500 mt-1">Để trống nếu không muốn thay đổi Secret hiện tại.</p>
                </div>
                
                @if(!$isCreate)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái Token</label>
                    <div class="px-3 py-2 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-600 font-mono">
                        {{ $shop->access_token ? 'Đã cấp quyền (Valid)' : 'Chưa có Token (Nhấn Re-authorize)' }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3 border-t border-gray-200 pt-6 mt-6">
            <a href="{{ route('btoc.shops') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                キャンセル 
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-md">
                保存 
            </button>
        </div>
    </form>
</div>
@endsection