<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Logs Viewer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body class="bg-gray-100 p-8" x-data="{ tab: 'api' }">

    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">System Logs Viewer</h1>

        <!-- Tabs -->
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                <li class="mr-2" role="presentation">
                    <button @click="tab = 'api'" :class="tab === 'api' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300'" class="inline-block p-4 border-b-2 rounded-t-lg" type="button">API Traffic Logs</button>
                </li>
                <li class="mr-2" role="presentation">
                    <button @click="tab = 'state'" :class="tab === 'state' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300'" class="inline-block p-4 border-b-2 rounded-t-lg" type="button">DB State Logs</button>
                </li>
            </ul>
        </div>

        <!-- Tab Contents -->
        <div id="myTabContent">
            
            <!-- API Traffic Tab -->
            <div x-show="tab === 'api'" class="p-4 rounded-lg bg-white shadow">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3">Time</th>
                                <th class="px-4 py-3">Method</th>
                                <th class="px-4 py-3">URL</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Duration</th>
                                <th class="px-4 py-3">Triggered Route</th>
                                <th class="px-4 py-3">Details</th>
                            </tr>
                        </thead>
                        @forelse($apiLogs as $log)
                        <tbody x-data="{ expanded: false }">
                            <tr class="border-b">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3 font-semibold">{{ $log->method }}</td>
                                <td class="px-4 py-3 max-w-xs truncate" title="{{ $log->url }}">{{ $log->url }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-bold {{ $log->response_status >= 200 && $log->response_status < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $log->response_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $log->duration_ms }} ms</td>
                                <td class="px-4 py-3 text-xs font-mono text-gray-500" title="{{ $log->triggered_by_url }}">{{ $log->triggered_by_route ?? 'N/A' }}</td>
                                <td class="px-4 py-3 space-x-2">
                                    <button @click="expanded = !expanded" class="text-blue-600 hover:underline">Toggle Details</button>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('debug.logs.api', $log->id) }}" target="_blank" class="text-blue-600 hover:underline" title="Open in new page">View Page &rarr;</a>
                                </td>
                            </tr>
                            <tr x-show="expanded" class="bg-gray-50">
                                <td colspan="6" class="px-4 py-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <h4 class="font-bold text-gray-700 mb-2">Request Payload</h4>
                                            <pre class="bg-gray-800 text-green-400 p-4 rounded text-xs overflow-auto max-h-64">{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                        <div>
                                            @php
                                                $decodedBody = is_string($log->response_body) ? json_decode($log->response_body, true) : $log->response_body;
                                                $displayBody = json_last_error() === JSON_ERROR_NONE ? json_encode($decodedBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : htmlspecialchars($log->response_body);
                                            @endphp
                                            <h4 class="font-bold text-gray-700 mb-2">Response Body</h4>
                                            <pre class="bg-gray-800 text-green-400 p-4 rounded text-xs overflow-auto max-h-64">{!! $displayBody !!}</pre>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        @empty
                        <tbody>
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center">No API logs found.</td>
                            </tr>
                        </tbody>
                        @endforelse
                    </table>
                </div>
                <div class="mt-4">
                    {{ $apiLogs->links() }}
                </div>
            </div>

            <!-- DB State Tab -->
            <div x-show="tab === 'state'" class="p-4 rounded-lg bg-white shadow" style="display: none;">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3">Time</th>
                                <th class="px-4 py-3">Model</th>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">User ID</th>
                                <th class="px-4 py-3">Triggered Route</th>
                                <th class="px-4 py-3">Details</th>
                            </tr>
                        </thead>
                        @forelse($stateLogs as $log)
                        <tbody x-data="{ expanded: false }">
                            <tr class="border-b">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3 font-semibold text-blue-600">{{ $log->model_type }}</td>
                                <td class="px-4 py-3">{{ $log->model_id }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded text-xs font-bold {{ $log->action == 'created' ? 'bg-green-100 text-green-800' : ($log->action == 'updated' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ strtoupper($log->action) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $log->user_id ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-xs font-mono text-gray-500" title="{{ $log->triggered_by_url }}">{{ $log->triggered_by_route ?? 'N/A' }}</td>
                                <td class="px-4 py-3 space-x-2">
                                    <button @click="expanded = !expanded" class="text-blue-600 hover:underline">Toggle State</button>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('debug.logs.state', $log->id) }}" target="_blank" class="text-blue-600 hover:underline" title="Open in new page">View Page &rarr;</a>
                                </td>
                            </tr>
                            <tr x-show="expanded" class="bg-gray-50">
                                <td colspan="6" class="px-4 py-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        @if($log->old_state)
                                        <div>
                                            <h4 class="font-bold text-gray-700 mb-2">Old State</h4>
                                            <pre class="bg-gray-800 text-yellow-400 p-4 rounded text-xs overflow-auto max-h-64">{{ json_encode($log->old_state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                        @endif
                                        @if($log->new_state)
                                        <div class="{{ !$log->old_state ? 'col-span-2' : '' }}">
                                            <h4 class="font-bold text-gray-700 mb-2">New State</h4>
                                            <pre class="bg-gray-800 text-green-400 p-4 rounded text-xs overflow-auto max-h-64">{{ json_encode($log->new_state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        @empty
                        <tbody>
                            <tr>
                                <td colspan="6" class="px-4 py-3 text-center">No state logs found.</td>
                            </tr>
                        </tbody>
                        @endforelse
                    </table>
                </div>
                <div class="mt-4">
                    {{ $stateLogs->links() }}
                </div>
            </div>

        </div>
    </div>
</body>
</html>
