<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Log Detail #{{ $log->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-800">API Log Detail #{{ $log->id }}</h1>
            <a href="{{ route('debug.logs') }}" class="text-blue-600 hover:underline">&larr; Back to Logs</a>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">URL</h3>
                    <p class="text-lg text-gray-900 break-all">{{ $log->url }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Method</h3>
                    <p class="text-lg text-gray-900"><span class="font-mono bg-gray-200 px-2 py-1 rounded">{{ $log->method }}</span></p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Status Code</h3>
                    <p class="text-lg">
                        <span class="px-3 py-1 rounded text-sm font-bold {{ $log->response_status >= 200 && $log->response_status < 300 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $log->response_status }}
                        </span>
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Duration</h3>
                    <p class="text-lg text-gray-900">{{ $log->duration_ms }} ms</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Time</h3>
                    <p class="text-lg text-gray-900">{{ $log->created_at->format('Y-m-d H:i:s') }}</p>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Triggered By URL</h3>
                    <p class="text-lg text-gray-900 break-all">{{ $log->triggered_by_url ?? 'N/A (Console/CLI)' }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Triggered By Route</h3>
                    <p class="text-lg text-gray-900"><span class="font-mono bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ $log->triggered_by_route ?? 'N/A' }}</span></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Headers -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Request Headers</h2>
                <pre class="bg-gray-800 text-yellow-400 p-4 rounded text-sm overflow-auto h-96">{{ json_encode($log->request_headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>

            <!-- Payload -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Request Payload</h2>
                <pre class="bg-gray-800 text-blue-400 p-4 rounded text-sm overflow-auto h-96">{{ json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>

            <!-- Response -->
            <div class="col-span-1 lg:col-span-2 bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Response Body</h2>
                @php
                    $decodedBody = is_string($log->response_body) ? json_decode($log->response_body, true) : $log->response_body;
                    $displayBody = json_last_error() === JSON_ERROR_NONE ? json_encode($decodedBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : htmlspecialchars($log->response_body);
                @endphp
                <pre class="bg-gray-800 text-green-400 p-4 rounded text-sm overflow-auto h-[600px]">{!! $displayBody !!}</pre>
            </div>
        </div>
    </div>
</body>
</html>
