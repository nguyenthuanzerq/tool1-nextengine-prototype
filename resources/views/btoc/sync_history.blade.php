@extends('layouts.app')

@section('content')
<div class="p-6 max-w-[1440px] mx-auto">

    <h1 class="text-2xl font-semibold text-gray-900 mb-6">
        同期履歴・ログ
        <span class="block text-sm text-gray-500 font-normal mt-1">
            Lịch sử đồng bộ & Log
        </span>
    </h1>

    {{-- AUTO SYNC SETTINGS --}}
    <form method="POST" action="#">
        @csrf
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">
                自動同期設定
                <span class="block text-sm text-gray-500 font-normal mt-1">
                    Cài đặt đồng bộ tự động
                </span>
            </h2>

            <div class="grid grid-cols-2 gap-6">

                <div>
                    <label class="block text-sm font-medium mb-3">
                        自動同期
                    </label>

                    <select name="auto_sync"
                            class="px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="1" {{ $autoSync ? 'selected' : '' }}>ON</option>
                        <option value="0" {{ !$autoSync ? 'selected' : '' }}>OFF</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">
                        同期間隔
                    </label>

                    <select name="sync_interval"
                            class="px-3 py-2 border border-gray-300 rounded-lg"
                            {{ !$autoSync ? 'disabled' : '' }}>
                        <option value="5" {{ $syncInterval == 5 ? 'selected' : '' }}>5分</option>
                        <option value="10" {{ $syncInterval == 10 ? 'selected' : '' }}>10分</option>
                        <option value="30" {{ $syncInterval == 30 ? 'selected' : '' }}>30分</option>
                    </select>
                </div>

            </div>
        </div>
    </form>

    {{-- SYNC HISTORY TABLE --}}
    <div class="bg-white rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">
                同期履歴
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            実行ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            ショップ
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            同期種類
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            開始時間
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            終了時間
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            ステータス
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700">
                            エラー内容
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                    @foreach($syncLogs as $log)
                        @php
                            $isSuccess = $log->status === '成功';
$isFail = $log->status === '失敗';
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm">
                                    <span class="text-gray-800">{{ $log->sync_code }}</span>
                                </td>

                            <td class="px-6 py-4 text-sm">
                                {{ $log->shop_name }}
                            </td>

                            <td class="px-6 py-4 text-sm">
                                {{ $log->sync_type }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-600">
                               {{ \Carbon\Carbon::parse($log->started_at)->format('Y-m-d H:i:s') }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $log->ended_at }}
                            </td>

                            <td class="px-6 py-4">
                                @if($isSuccess)
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">
                                        成功
                                    </span>
                                @elseif($isFail)
                                    <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">
                                        失敗
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">
                                        実行中
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-red-600">
                                {{ $log->error_message ?? '-' }}
                            </td>
                        </tr>

                    @endforeach
             </tbody>
</table>

<div class="mt-4">
    {{ $syncLogs->links() }}
</div>

</div>
</div>

</div>
@endsection
