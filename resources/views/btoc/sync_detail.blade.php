@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">

    <a href="{{ route('btoc.sync.history') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 mb-6">
        ← 戻る (Quay lại)
    </a>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">
            同期詳細
            <span class="block text-sm text-gray-500 font-normal mt-1">
                Chi tiết đồng bộ
            </span>
        </h1>

        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
            再実行 (Retry Sync)
        </button>
    </div>

    @php
        $isError = $syncDetail['status'] === '失敗';
    @endphp

    {{-- ERROR ALERT --}}
    @if($isError)
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="text-sm font-medium text-red-900">
                同期エラーが発生しました
            </div>
            <div class="text-sm text-red-800 mt-2">
                {{ $syncDetail['error'] }}
            </div>
        </div>
    @endif

    {{-- SYNC INFO --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">同期情報</h2>

        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-gray-500">実行ID</div>
                <div>{{ $syncDetail['syncId'] }}</div>
            </div>

            <div>
                <div class="text-gray-500">ショップ</div>
                <div>{{ $syncDetail['shopName'] }} ({{ $syncDetail['shopId'] }})</div>
            </div>

            <div>
                <div class="text-gray-500">同期種類</div>
                <div>{{ $syncDetail['syncType'] }}</div>
            </div>

            <div>
                <div class="text-gray-500">ステータス</div>
                <div>
                    @if($isError)
                        <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">
                            失敗
                        </span>
                    @else
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">
                            成功
                        </span>
                    @endif
                </div>
            </div>

            <div>
                <div class="text-gray-500">開始時間</div>
                <div>{{ $syncDetail['startTime'] }}</div>
            </div>

            <div>
                <div class="text-gray-500">終了時間</div>
                <div>{{ $syncDetail['endTime'] }}</div>
            </div>
        </div>
    </div>

    {{-- API REQUEST --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">APIリクエスト</h2>

        <div class="space-y-3 text-sm">

            <div>
                <div class="text-gray-500 mb-1">Endpoint</div>
                <div class="bg-gray-50 p-2 border rounded font-mono">
                    {{ $syncDetail['apiRequest']['method'] }}
                    {{ $syncDetail['apiRequest']['endpoint'] }}
                </div>
            </div>

            <div>
                <div class="text-gray-500 mb-1">Headers</div>
                <pre class="bg-gray-50 p-2 border rounded text-xs font-mono">{{ json_encode($syncDetail['apiRequest']['headers'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>

            <div>
                <div class="text-gray-500 mb-1">Body</div>
                <pre class="bg-gray-50 p-2 border rounded text-xs font-mono">{{ json_encode($syncDetail['apiRequest']['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>

        </div>
    </div>

    {{-- API RESPONSE --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <h2 class="text-lg font-semibold mb-4">APIレスポンス</h2>

        <div class="space-y-3 text-sm">

            <div>
                <div class="text-gray-500 mb-1">Status Code</div>
                <span class="px-3 py-1 rounded text-xs font-medium
                    {{ $isError ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                    {{ $syncDetail['apiResponse']['status'] }}
                </span>
            </div>

            <div>
                <div class="text-gray-500 mb-1">Response Body</div>
                <pre class="p-2 border rounded text-xs font-mono
                    {{ $isError ? 'bg-red-50 border-red-200 text-red-900' : 'bg-gray-50 border-gray-200 text-gray-900' }}">
{{ json_encode($isError ? $syncDetail['apiResponse']['error'] : $syncDetail['apiResponse'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                </pre>
            </div>

        </div>
    </div>

</div>
@endsection