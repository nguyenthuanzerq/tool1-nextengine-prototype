<?php

namespace App\Listeners;

use App\Models\ApiTrafficLog;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;

class LogApiTrafficListener
{
    /**
     * Handle the event.
     */
    public function handle(ResponseReceived|ConnectionFailed $event): void
    {
        // Only log if debug logging is enabled
        if (!config('app.debug_logs', env('ENABLE_DEBUG_LOGS', false))) {
            return;
        }

        $request = $event->request;
        $response = $event instanceof ResponseReceived ? $event->response : null;

        $payload = $request->data();
        if (empty($payload)) {
            $body = $request->body();
            if ($body) {
                $decoded = json_decode($body, true);
                $payload = json_last_error() === JSON_ERROR_NONE ? $decoded : $body;
            }
        }

        $duration = 0;
        if ($response && method_exists($response, 'handlerStats')) {
            $stats = $response->handlerStats();
            if (isset($stats['total_time'])) {
                $duration = $stats['total_time'] * 1000;
            }
        }

        ApiTrafficLog::create([
            'url' => $request->url(),
            'method' => $request->method(),
            'request_headers' => $request->headers(),
            'request_payload' => $payload,
            'response_status' => $response ? $response->status() : 500,
            'response_body' => $response ? $response->body() : 'Connection Failed',
            'duration_ms' => $duration ?: null,
            'triggered_by_url' => request()->url(),
            'triggered_by_route' => request()->route() ? request()->route()->getName() : null,
        ]);
    }
}
