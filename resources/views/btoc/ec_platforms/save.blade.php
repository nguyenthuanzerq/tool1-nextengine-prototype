@extends('layouts.app')

@section('content')
<div class="p-6 max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            {{ $isCreate ? 'プラットフォーム新規作成' : 'プラットフォーム編集' }}
            <span class="block text-sm text-gray-500 font-normal mt-1">
                {{ $isCreate ? 'Thêm nền tảng mới' : 'Chỉnh sửa nền tảng: ' . $platform->name }}
            </span>
        </h1>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form action="{{ $isCreate ? route('btoc.platforms.store') : route('btoc.platforms.update', $platform->id) }}"
              method="POST">
            @csrf
            @unless($isCreate)
                @method('PUT')
            @endunless

            {{-- Name --}}
            <div class="mb-5">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    プラットフォーム名 <span class="text-gray-400">(Name)</span>
                    <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name', $platform->name) }}"
                       placeholder="例: Next Engine, Rakuten"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Code --}}
            <div class="mb-5">
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                    プラットフォームコード <span class="text-gray-400">(Code)</span>
                    <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="code"
                       name="code"
                       value="{{ old('code', $platform->code) }}"
                       placeholder="例: nextengine, rakuten, shopify"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition @error('code') border-red-500 @enderror">
                @error('code')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-400">英小文字・アンダースコアのみ推奨 (Nên dùng chữ thường và dấu gạch dưới)</p>
            </div>

            {{-- Auth Type --}}
            <div class="mb-6">
                <label for="auth_type" class="block text-sm font-medium text-gray-700 mb-1">
                    認証タイプ <span class="text-gray-400">(Auth Type)</span>
                    <span class="text-red-500">*</span>
                </label>
                <select id="auth_type"
                        name="auth_type"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white @error('auth_type') border-red-500 @enderror">
                    <option value="" disabled {{ old('auth_type', $platform->auth_type) ? '' : 'selected' }}>
                        -- 選択してください (Chọn loại) --
                    </option>
                    <option value="oauth2" {{ old('auth_type', $platform->auth_type) === 'oauth2' ? 'selected' : '' }}>
                        OAuth2
                    </option>
                    <option value="api_key" {{ old('auth_type', $platform->auth_type) === 'api_key' ? 'selected' : '' }}>
                        API Key
                    </option>
                </select>
                @error('auth_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('btoc.platforms.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                    キャンセル (Hủy)
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm">
                    {{ $isCreate ? '作成する (Tạo mới)' : '更新する (Cập nhật)' }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
