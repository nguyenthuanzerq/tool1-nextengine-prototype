<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Btoc\InventoryService;
use App\Models\Shop;
use App\Models\Mall;
use App\Models\Inventory;

class InventoryController extends Controller
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    // inventoryShipment
    public function inventoryShipment(Request $request)
    {
        // 1) Filters cho inventory
        $filters = $request->only([
            'shop_id',
            'mall_id',
            'product_code',
        ]);

        // 2) Data inventory + shipment
      $inventoryData = Inventory::with(['mall.shop'])
    ->filter($filters)
    ->orderByDesc('id')
    ->get();
        $shipmentData  = $this->inventoryService->getShipment();

        // 3) Dropdown data
        $shops = Shop::query()->orderBy('id')->get();

        // Nếu chọn shop_id -> chỉ lấy malls của shop đó
        $malls = Mall::query()
            ->when($request->shop_id, function ($q) use ($request) {
                $q->where('shop_id', $request->shop_id);
            })
            ->orderBy('id')
            ->get();

        return view('btoc.inventory_shipment', compact(
            'inventoryData',
            'shipmentData',
            'shops',
            'malls'
        ));
    }

    public function refreshInventory()
    {
        $this->inventoryService->refreshInventory();

        return redirect()->route('btoc.inventory')
            ->with('success', 'リアルタイム更新しました。');
    }
}