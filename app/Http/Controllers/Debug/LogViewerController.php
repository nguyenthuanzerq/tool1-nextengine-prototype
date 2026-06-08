<?php

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use App\Models\ApiTrafficLog;
use App\Models\StateLog;
use Illuminate\Http\Request;

class LogViewerController extends Controller
{
    public function index(Request $request)
    {
        // Require ENABLE_DEBUG_LOGS to be true to access this page
        if (!config('app.debug_logs', env('ENABLE_DEBUG_LOGS', false))) {
            abort(404);
        }

        $apiLogs = ApiTrafficLog::orderBy('created_at', 'desc')->paginate(20, ['*'], 'api_page');
        $stateLogs = StateLog::orderBy('created_at', 'desc')->paginate(20, ['*'], 'state_page');

        return view('debug.logs', compact('apiLogs', 'stateLogs'));
    }
    public function apiDetail($id)
    {
        if (!config('app.debug_logs', env('ENABLE_DEBUG_LOGS', false))) {
            abort(404);
        }

        $log = ApiTrafficLog::findOrFail($id);
        return view('debug.api_detail', compact('log'));
    }

    public function stateDetail($id)
    {
        if (!config('app.debug_logs', env('ENABLE_DEBUG_LOGS', false))) {
            abort(404);
        }

        $log = StateLog::findOrFail($id);
        return view('debug.state_detail', compact('log'));
    }
}

