<?php

namespace App\Services\Btoc;

use App\Models\Inventory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Lấy danh sách tồn kho có hỗ trợ filter
     * Không phá cấu trúc Phase 1–8
     */
    public function getInventory(array $filters = [])
{
    return Inventory::with(['mall.shop'])
        ->filter($filters)
        ->orderByDesc('id')
        ->get();
}

    /**
     * Refresh inventory timestamp (giữ đúng logic hiện tại)
     */
    public function refreshInventory(): void
    {
        Inventory::query()->update([
            'last_updated' => now(),
        ]);
    }

    /**
     * Lấy danh sách shipment
     * Giữ nguyên cấu trúc cũ (chưa có Model)
     */
    public function getShipment(): Collection
    {
        return DB::table('shipments')
            ->orderByDesc('id')
            ->get();
    }
}