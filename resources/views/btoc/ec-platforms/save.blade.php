@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">
    {{-- Back Link --}}
    <a href="{{ route('btoc.ec-platforms.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6 transition">
        ← プラットフォームリストに戻る (Quay lại danh sách)
    </a>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            {{ $platform->exists ? 'ECプラットフォーム編集' : 'ECプラットフォーム追加' }}
            <span class="block text-sm text-gray-500 font-normal mt-1">
                {{ $platform->exists ? 'Chỉnh sửa Nền tảng EC' : 'Thêm Nền tảng EC mới' }}
            </span>
        </h1>
    </div>

    {{-- Main Form Card (Bắt chước cấu trúc save.blade.php mẫu) --}}
    <form action="{{ $platform->exists ? route('btoc.ec-platforms.update', $platform->id) : route('btoc.ec-platforms.store') }}" method="POST">
        @csrf
        @if($platform->exists) @method('PUT') @endif
        
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            {{-- Form Header có nút Save (Pin ở góc phải) --}}
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-base font-semibold text-gray-800">
                    プラットフォーム設定 <span class="text-xs text-gray-500 font-normal">(Cấu hình Nền tảng)</span>
                </h2>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition shadow-sm">
                        {{ $platform->exists ? '更新する (Cập nhật)' : '保存する (Lưu lại)' }}
                    </button>
                </div>
            </div>

            {{-- Form Body - Split Layout (Bắt chước mẫu) --}}
            <div class="p-8">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-10">
                    
                    {{-- Khối thông tin mô tả bên trái --}}
                    {{-- <div class="xl:col-span-1 space-y-3">
                        <h3 class="text-sm font-semibold text-gray-800 border-b border-gray-200 pb-2">基本情報 (Thông tin cơ bản)</h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            連携するECプラットフォーム（例：Shopify, Yahoo Shopping）の基本情報を入力します。<br>
                            (Nhập thông tin cơ bản của nền tảng EC muốn liên kết, ví dụ: Shopify, Yahoo Shopping).
                        </p>
                        <p class="text-xs text-gray-500 bg-gray-50 p-3 rounded-md border border-gray-200">
                            ※ <strong class="text-red-600">ID (Code)</strong> はシステム内でプラットフォームを識別するためのユニークな値です。保存後は変更できません。<br>
                            (※ <strong class="text-red-600">ID (Code)</strong> là giá trị duy nhất để định danh nền tảng trong hệ thống. Không thể thay đổi sau khi lưu).
                        </p>
                    </div> --}}

                    {{-- Khối Input Form bên phải --}}
                    <div class="xl:col-span-2 space-y-6">
                        
                        {{-- Row 1: Code & Name --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">ID (Code) *</label>
                                <input type="text" name="code" value="{{ old('code', $platform->code) }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm @error('code') border-red-500 @enderror" 
                                       placeholder="例: shopify, yahoo_jp"
                                       {{ $platform->exists ? 'readonly bg-gray-100 cursor-not-allowed' : 'required' }}>
                                @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">プラットフォーム名 (Tên nền tảng) *</label>
                                <input type="text" name="name" value="{{ old('name', $platform->name) }}" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm @error('name') border-red-500 @enderror" 
                                       placeholder="例: Shopify" required>
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Row 2: Auth Type & Status --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">認証タイプ (Loại xác thực API) *</label>
                                <select name="auth_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm" required>
                                    <option value="oauth2" {{ old('auth_type', $platform->auth_type) == 'oauth2' ? 'selected' : '' }}>OAuth 2.0 (Token based)</option>
                                    <option value="api_key" {{ old('auth_type', $platform->auth_type) == 'api_key' ? 'selected' : '' }}>API Key / Access Token</option>
                                    <option value="basic" {{ old('auth_type', $platform->auth_type) == 'basic' ? 'selected' : '' }}>Basic Auth (User/Pass)</option>
                                </select>
                            </div>
                            {{-- Checkbox Status --}}
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="sr-only peer" {{ old('is_active', $platform->is_active ?? 1) ? 'checked' : '' }}>
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
                                    <span class="ml-3 text-sm font-medium text-gray-800">利用可能にする (Kích hoạt nền tảng này)</span>
                                </label>
                            </div>
                        </div>

                        {{-- Row 3: Website URL --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Trang chủ URL (Không bắt buộc)</label>
                            <input type="url" name="website_url" value="{{ old('website_url', $platform->website_url) }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm" 
                                   placeholder="https://">
                            @error('website_url') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
                
                {{-- Phần Footer của Body Form (Tùy chọn) --}}
                <div class="mt-10 pt-6 border-t border-gray-100 flex justify-end">
                     <a href="{{ route('btoc.ec-platforms.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                        キャンセル (Hủy)
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection