<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
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
