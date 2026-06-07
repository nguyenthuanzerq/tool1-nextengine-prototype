<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>State Log Detail #{{ $log->id }}</title>
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
            <h1 class="text-3xl font-bold text-gray-800">State Log Detail #{{ $log->id }}</h1>
            <a href="{{ route('debug.logs') }}" class="text-blue-600 hover:underline">&larr; Back to Logs</a>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Model Type</h3>
                    <p class="text-lg font-bold text-blue-600">{{ $log->model_type }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Model ID</h3>
                    <p class="text-lg text-gray-900">{{ $log->model_id }}</p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">Action</h3>
                    <p class="text-lg">
                        <span class="px-3 py-1 rounded text-sm font-bold {{ $log->action == 'created' ? 'bg-green-100 text-green-800' : ($log->action == 'updated' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ strtoupper($log->action) }}
                        </span>
                    </p>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-500 uppercase">User ID</h3>
                    <p class="text-lg text-gray-900">{{ $log->user_id ?? 'System / N/A' }}</p>
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
            @if($log->old_state)
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Old State</h2>
                <pre class="bg-gray-800 text-yellow-400 p-4 rounded text-sm overflow-auto h-[600px]">{{ json_encode($log->old_state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
            
            @if($log->new_state)
            <div class="bg-white shadow rounded-lg p-6 {{ !$log->old_state ? 'lg:col-span-2' : '' }}">
                <h2 class="text-xl font-bold text-gray-800 mb-4">New State</h2>
                <pre class="bg-gray-800 text-green-400 p-4 rounded text-sm overflow-auto h-[600px]">{{ json_encode($log->new_state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
