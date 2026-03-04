@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">

    {{-- Back --}}
    <a href="{{ route('btoc.shops') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6">
        ← 戻る (Quay lại)
    </a>

    <h1 class="text-2xl font-semibold text-gray-900 mb-6">
        ショップ詳細
        <span class="block text-sm text-gray-500 font-normal mt-1">
            Chi tiết Shop
        </span>
    </h1>

    {{-- Token Expired Alert --}}
    @if(($mode ?? 'create') === 'edit' 
        && isset($shop) 
        && ($shop->connection_status ?? '') === 'expired')
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="text-sm font-medium text-red-900">
                トークンが期限切れです。再認証してください。
            </div>
            <div class="text-xs text-red-700 mt-1">
                Token đã hết hạn. Vui lòng xác thực lại.
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $action }}">
        @csrf
        @if(($mode ?? 'create') === 'edit')
            @method('PUT')
        @endif

        <div class="space-y-6">

            {{-- Basic Info --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    基本情報
                    <span class="block text-sm text-gray-500 font-normal mt-1">
                        Thông tin cơ bản
                    </span>
                </h2>

                <div class="grid grid-cols-2 gap-4">

                    {{-- Shop Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            ショップ名
                            <span class="block text-xs text-gray-500">Tên shop</span>
                        </label>
                        <input type="text"
                               name="shop_name"
                               value="{{ old('shop_name', $shop->shop_name ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>

                    {{-- Shop Code --}}
                    <div>
   <label class="block text-sm font-medium text-gray-700 mb-2">
    ShopID
    <span class="block text-xs text-gray-500">
        Mã shop
    </span>
</label>

    <input type="text"
        name="shop_code"
        value="{{ old('shop_code', $shop->shop_code ?? '') }}"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        {{ ($mode ?? 'create') === 'edit' ? 'readonly' : '' }}
        required>
</div>

                </div>
            </div>

            {{-- NextEngine Auth --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    NextEngine認証情報
                    <span class="block text-sm text-gray-500 font-normal mt-1">
                        Thông tin xác thực NextEngine
                    </span>
                </h2>

                <div class="grid grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Client ID
                        </label>
                        <input type="text"
                               name="client_id"
                               value="{{ old('client_id', $shop->client_id ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Client Secret
                        </label>
                        <input type="password"
                               name="client_secret"
                               value="{{ old('client_secret', $shop->client_secret ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Login ID
                        </label>
                        <input type="text"
                               name="login_id"
                               value="{{ old('login_id', $shop->login_id ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Login Password
                        </label>
                        <input type="password"
                               name="login_password"
                               value="{{ old('login_password', $shop->login_password ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>

                </div>

                <div class="flex gap-3 mt-4">
                    <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        接続テスト (Test connection)
                    </button>

                    <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        再認証 (Re-authorize)
                    </button>

                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
                        保存 (Save)
                    </button>
                </div>
            </div>

            {{-- Token Details --}}
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">
                    トークン詳細
                    <span class="block text-sm text-gray-500 font-normal mt-1">
                        Chi tiết Token
                    </span>
                </h2>

                <div class="space-y-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Access Token
                        </label>
                        <input type="password"
                               value="{{ $shop->access_token ?? '' }}"
                               readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Refresh Token
                        </label>
                        <input type="password"
                               value="{{ $shop->refresh_token ?? '' }}"
                               readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            有効期限
                            <span class="block text-xs text-gray-500">
                                Thời hạn token
                            </span>
                        </label>
                        <input type="text"
                               value="{{ $shop->token_expires_at ?? '' }}"
                               readonly
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                    </div>

                    <button type="button"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm">
                        トークン更新 (Refresh token)
                    </button>

                </div>
            </div>

        </div>
    </form>

</div>
@endsection