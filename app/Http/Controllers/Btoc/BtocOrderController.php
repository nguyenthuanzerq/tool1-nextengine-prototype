<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Btoc\OrderService;
use App\DTO\Btoc\RegisterTrackingDTO;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SyncHistory;

class BtocOrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
       $orders = Order::with('orderProducts')->get();

    return view('btoc.kanri_gamen', compact('orders'));
    }

    public function dashboard()
{
      $shopCount = \App\Models\Shop::count();

    $todayOrders = \App\Models\Order::whereDate('created_at', today())->count();

    $unshipped = \App\Models\Order::where('status', 'pending')->count();

    $todayShipped = \App\Models\Order::whereDate('shipped_at', today())->count();

    $shops = Shop::all();

    return view('btoc.dashboard', compact(
        'shopCount',
        'todayOrders',
        'unshipped',
        'todayShipped',
        'shops',
    ));
}

    public function registerTracking(Request $request)
    {
        $validated = $request->validate([
            'order_id'       => 'required|integer',
            'tracking_number'=> 'required|string|max:255',
        ]);

        $dto = new RegisterTrackingDTO(
            (int) $validated['order_id'],
            (string) $validated['tracking_number']
        );

        $this->orderService->registerTracking($dto);

       return redirect()
    ->route('btoc.index')
    ->with('success', '発送番号を登録しました。');
    }

    public function shops()
{
     $shops = Shop::all();   // Lấy toàn bộ shop từ DB

    return view('btoc.shops', compact('shops'));
}

public function shopCreate()
{
    return view('btoc.shop_detail', [
        'shop'   => new \App\Models\Shop(),   // <- để không undefined
        'mode'   => 'create',
        'action' => route('btoc.shop.store'),
    ]);
}

public function shopEdit($id)
{
    $shop = \App\Models\Shop::findOrFail($id);

    return view('btoc.shop_detail', [
        'shop'   => $shop,
        'mode'   => 'edit',
        'action' => route('btoc.shop.update', $id),
    ]);
}

public function shopStore(Request $request)
{
    // dd($request->all());
    Shop::create([
        'shop_code'     => $request->shop_code,
        'shop_name'     => $request->shop_name,
        'client_id'     => $request->client_id,
        'client_secret' => $request->client_secret,
        'login_id'      => $request->login_id,
        'login_password'=> $request->login_password,
    ]);

    return redirect()->route('btoc.shops');
}

public function shopUpdate(Request $request, $id)
{
    $shop = Shop::findOrFail($id);
    $shop->update($request->all());
    return redirect()->route('btoc.shops');
}

public function show($id)
{
    $shop = Shop::where('shop_code', $id)->firstOrFail();
    return view('btoc.shop_detail', compact('shop'));
}

// inventoryShipment
public function inventoryShipment()
{
    $inventoryData = DB::table('inventories')
        ->orderByDesc('id')
        ->get();

    $shipmentData = DB::table('shipments')
        ->orderByDesc('id')
        ->get();

    return view('btoc.inventory_shipment', compact('inventoryData', 'shipmentData'));
}


// emailSettings
public function emailSettings(Request $request)
{
    $templateName = "発送通知メール";

    $subject = "【{carrier}】ご注文商品を発送しました - 送り状番号: {tracking_number}";

    $body = "{customer_name} 様

いつもご利用いただき、誠にありがとうございます。

ご注文いただきました商品を発送いたしましたので、お知らせいたします。

【注文情報】
注文ID: {order_id}
配送業者: {carrier}
送り状番号: {tracking_number}";

    $previewData = [
        "customer_name" => "山田太郎",
        "order_id" => "ORD-2026-001",
        "tracking_number" => "123456789012",
        "carrier" => "ヤマト運輸",
    ];

    $showPreview = false;
    $previewSubject = $subject;
    $previewBody = $body;

    if ($request->has('preview')) {
        $showPreview = true;

        foreach ($previewData as $key => $value) {
            $previewSubject = str_replace("{{$key}}", $value, $previewSubject);
            $previewBody = str_replace("{{$key}}", $value, $previewBody);
        }
    }

    return view('btoc.email_settings', compact(
        'templateName',
        'subject',
        'body',
        'showPreview',
        'previewSubject',
        'previewBody'
    ));
}

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
 
public function refreshInventory()
{
    // giả lập realtime update
    DB::table('inventories')->update([
        'last_updated' => now()
    ]);

    return redirect()->route('btoc.inventory')
        ->with('success', 'リアルタイム更新しました。');
}

}
