<?php

namespace App\Services\Btoc;

use App\Models\Inventory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Architecture-first:
     * Toàn bộ query logic nằm ở Service layer
     */
    public function filter(array $filters = [])
    {
        return Inventory::query()
            ->with(['mall.channel', 'mall.shop'])

            // Filter theo Shop
            ->when($filters['shop_id'] ?? null, function ($q, $shopId) {
                $q->whereHas('mall.shop', function ($q2) use ($shopId) {
                    $q2->where('id', $shopId);
                });
            })

            // Filter theo Mall
            ->when($filters['mall_id'] ?? null, function ($q, $mallId) {
                $q->where('mall_id', $mallId);
            })

            // Filter theo Channel
            ->when($filters['channel_id'] ?? null, function ($q, $channelId) {
                $q->whereHas('mall', function ($q2) use ($channelId) {
                    $q2->where('channel_id', $channelId);
                });
            })

            // Filter theo Product Code
            ->when($filters['product_code'] ?? null, function ($q, $code) {
                $q->where('product_code', 'like', "%{$code}%");
            })

            ->orderByDesc('id')
            ->get();
    }

    /**
     * Giữ tương thích cấu trúc cũ
     */
    public function getInventory(array $filters = [])
    {
        return $this->filter($filters);
    }

    /**
     * Refresh inventory
     */
    public function refreshInventory(): void
    {
        Inventory::query()->update([
            'last_updated' => now(),
        ]);
    }

    /**
     * Shipment list (giữ nguyên)
     */
    public function getShipment(): Collection
    {
        return DB::table('shipments')
            ->orderByDesc('id')
            ->get();
    }
}
