<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\Channel;
use App\Models\Mall;
use App\Models\Shop;
use App\Services\Btoc\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function inventoryShipment(Request $request)
    {
        $filters = $request->only([
            'shop_id',
            'mall_id',
            'channel_id',
            'product_code',
        ]);

        $inventoryData = $this->inventoryService->filter($filters);
        $shipmentData = $this->inventoryService->getShipment();
        $shops = Shop::query()->orderBy('id')->get();

        $malls = Mall::query()
            ->when($request->shop_id, function ($q) use ($request) {
                $q->where('shop_id', $request->shop_id);
            })
            ->orderBy('id')
            ->get();

        $channels = Channel::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $channelTbdList = [
            [
                'jp' => 'Channel は管理画面から追加可能にしますか？',
                'vn' => 'Channel có cho phép thêm mới từ màn hình Admin không?',
            ],
            [
                'jp' => 'Channel ごとに API 認証情報（Client ID / Secret）は必要ですか？',
                'vn' => 'Mỗi Channel có cần credential riêng không?',
            ],
            [
                'jp' => '非アクティブの Channel はフィルターから非表示にしますか？',
                'vn' => 'Channel inactive có ẩn khỏi filter không?',
            ],
        ];

        return view('btoc.inventory_shipment', compact(
            'inventoryData',
            'shipmentData',
            'shops',
            'malls',
            'channels',
            'channelTbdList'
        ));
    }

    public function refreshInventory()
    {
        $this->inventoryService->refreshInventory();

        return redirect()->route('btoc.inventory')
            ->with('success', 'リアルタイム更新しました。');
    }
}
