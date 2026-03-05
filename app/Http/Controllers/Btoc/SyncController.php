<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SyncHistory;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
   // SyncDetail
public function syncDetail($id)
{
    $syncDetail = [
        "syncId" => $id,
        "shopName" => "Yahoo!ショッピング店",
        "shopId" => "SHOP003",
        "syncType" => "商品同期",
        "startTime" => "2026-02-20 09:05:10",
        "endTime" => "2026-02-20 09:05:45",
        "status" => "失敗",
        "error" => "認証エラー: トークンが無効です",
        "apiRequest" => [
            "endpoint" => "/api/v1/nextengine/products/sync",
            "method" => "POST",
            "headers" => [
                "Authorization" => "Bearer xxxx",
                "Content-Type" => "application/json",
            ],
            "body" => [
                "shop_id" => "SHOP003",
                "sync_type" => "products",
                "limit" => 100,
            ],
        ],
        "apiResponse" => [
            "status" => 401,
            "error" => [
                "code" => "INVALID_TOKEN",
                "message" => "The access token is invalid or expired",
            ],
        ],
    ];

    return view('btoc.sync_detail', compact('syncDetail'));
}

// syncHistory
public function syncHistory()
{
    $autoSync = true;
    $syncInterval = 10;

    $syncLogs = SyncHistory::orderByDesc('id')->paginate(20);

    return view('btoc.sync_history', compact(
        'autoSync',
        'syncInterval',
        'syncLogs'
    ));
}

public function manualSync()
{
    Log::info('MANUAL_SYNC_TRIGGERED', [
        'timestamp' => now()
    ]);

    SyncHistory::create([
       'sync_code' => 'SYNC-' . now()->format('YmdHisv'),
        'shop_name'     => '楽天市場店',   // sau này lấy từ shop table
        'sync_type'     => '注文同期',
        'started_at'    => now(),
        'ended_at'      => now(),
        'status'        => '成功',
        'error_message' => null,
    ]);

    return redirect()->route('btoc.sync.history')
        ->with('success', '手動同期を実行しました。');
}
}
