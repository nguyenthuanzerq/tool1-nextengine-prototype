@extends('layouts.app')

@section('content')
    <div class="max-w-[1440px]">
        <a href="{{ route('btoc.shop.index') }}"
            class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800 mb-5 transition">
            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M15 18l-6-6 6-6" />
            </svg>
            ショップ一覧に戻る
        </a>

        @if ($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-600">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-6">
            <h1 class="text-xl font-bold text-gray-900">
                {{ $isCreate ? '新規ショップ追加' : 'ショップ編集' }}
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $isCreate ? 'Add New Shop' : 'Edit Shop' }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Left: Basic Info --}}
            <div>
                <h2 class="text-sm font-semibold text-gray-700 mb-3">基本情報 / Basic Info</h2>
                <form method="POST"
                    action="{{ $isCreate ? route('btoc.shop.store') : route('btoc.shop.update', $shop->id) }}">
                    @csrf
                    @if (!$isCreate)
                        @method('PUT')
                    @endif

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ショップコード <span class="text-red-500">*</span></label>
                            <input type="text" name="shop_code" value="{{ old('shop_code', $shop->shop_code) }}"
                                placeholder="例: shop-001"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            <p class="text-xs text-gray-400 mt-1">Shop Code</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ショップ名 <span class="text-red-500">*</span></label>
                            <input type="text" name="shop_name" value="{{ old('shop_name', $shop->shop_name) }}"
                                placeholder="例: NextEngineショップ"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            <p class="text-xs text-gray-400 mt-1">Shop Name</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">プラットフォーム <span class="text-red-500">*</span></label>
                            <select name="platform_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                                <option value="">-- プラットフォームを選択 --</option>
                                @foreach ($platforms as $platform)
                                    <option value="{{ $platform->id }}"
                                        {{ old('platform_id', $shop->platform_id) == $platform->id ? 'selected' : '' }}>
                                        {{ $platform->name }} ({{ $platform->auth_type }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Platform</p>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-5">
                        <a href="{{ route('btoc.shop.index') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                            キャンセル
                        </a>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                            <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                <polyline points="17 21 17 13 7 13 7 21" />
                                <polyline points="7 3 7 8 15 8" />
                            </svg>
                            保存
                        </button>
                    </div>
                </form>
            </div>

            {{-- Right: Platform Credentials (edit mode only) --}}
            @if (!$isCreate)
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 mb-3">API 認証情報 / Platform Credentials</h2>

                    {{-- Pass platform auth_type map to JS --}}
                    @php
                        $platformMap = $platforms->mapWithKeys(fn($p) => [$p->id => ['auth_type' => $p->auth_type, 'name' => $p->name]])->toJson();
                        $authType = $shop->platform?->auth_type ?? 'oauth2';
                    @endphp
                    <script>
                        const PLATFORM_MAP = @json($platforms->mapWithKeys(fn($p) => [$p->id => ['auth_type' => $p->auth_type, 'name' => $p->name]]));
                    </script>

                    <form method="POST" action="{{ route('btoc.shop.nextengine_connection', $shop->id) }}">
                        @csrf
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">

                            {{-- Platform badge (dynamic) --}}
                            <div class="flex items-center gap-2 pb-3 border-b border-gray-100" id="platform-badge">
                                @if($shop->platform)
                                <span class="text-xs text-gray-500">Platform:</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700" id="badge-label">
                                    {{ $shop->platform->name }} ({{ $shop->platform->auth_type }})
                                </span>
                                @endif
                            </div>

                            {{-- oauth2: Client ID + Client Secret --}}
                            <div id="block-oauth2" class="{{ $authType !== 'api_key' ? '' : 'hidden' }} space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Client ID <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="client_id"
                                        value="{{ old('client_id', $connection?->client_id) }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Client Secret <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="client_secret"
                                        value="{{ old('client_secret', $connection?->client_secret) }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                {{-- Read-only tokens --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Access Token</label>
                                    <input type="text" value="{{ $connection?->access_token ? '設定済み (set)' : '—' }}"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-500 bg-gray-50" readonly>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Refresh Token</label>
                                    <input type="text" value="{{ $connection?->refresh_token ? '設定済み (set)' : '—' }}"
                                        class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs text-gray-500 bg-gray-50" readonly>
                                </div>
                            </div>

                            {{-- api_key: API Key (client_id) + Secret (client_secret) --}}
                            <div id="block-apikey" class="{{ $authType === 'api_key' ? '' : 'hidden' }} space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        API Key (Service Secret) <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="client_id"
                                        value="{{ old('client_id', $connection?->client_id) }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        License Key <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="client_secret"
                                        value="{{ old('client_secret', $connection?->client_secret) }}"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                        </div>

                        <div class="flex flex-wrap gap-3 mt-4">
                            <button type="submit"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                </svg>
                                認証情報を保存
                            </button>

                            {{-- OAuth2: show connect button after credentials saved --}}
                            @if($connection?->client_id && $authType !== 'api_key')
                                <a href="{{ route('nextengine.connect', ['id' => $shop->id]) }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-green-600 hover:bg-green-700 text-white transition">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                                    </svg>
                                    OAuth 連携 / Connect
                                </a>
                            @endif

                            {{-- Link to Sync tab after token obtained --}}
                            @if($connection?->access_token)
                                <a href="{{ route('btoc.shop.show', $shop->id) }}?tab=sync"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 4v6h6M23 20v-6h-6" /><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10M23 14l-4.64 4.36A9 9 0 0 1 3.51 15" />
                                    </svg>
                                    同期タブへ
                                </a>
                            @endif
                        </div>

                    </form>
                </div>
            @endif
        </div>
    </div>

    <script>
        (function () {
            const sel = document.querySelector('select[name="platform_id"]');
            if (!sel) return;

            function applyAuthType(platformId) {
                const info = PLATFORM_MAP[platformId];
                const authType = info ? info.auth_type : 'oauth2';
                const name = info ? info.name : '';

                const blockOauth2 = document.getElementById('block-oauth2');
                const blockApikey = document.getElementById('block-apikey');
                const isApiKey = authType === 'api_key';

                blockOauth2.classList.toggle('hidden', isApiKey);
                blockApikey.classList.toggle('hidden', !isApiKey);

                // Disable inputs in hidden block so they don't overwrite submitted values
                blockOauth2.querySelectorAll('input').forEach(el => el.disabled = isApiKey);
                blockApikey.querySelectorAll('input').forEach(el => el.disabled = !isApiKey);

                const badge = document.getElementById('badge-label');
                if (badge && name) {
                    badge.textContent = name + ' (' + authType + ')';
                }
            }

            sel.addEventListener('change', function () {
                applyAuthType(this.value);
            });

            // Apply on page load (edit mode)
            applyAuthType(sel.value);
        })();
    </script>
@endsection
