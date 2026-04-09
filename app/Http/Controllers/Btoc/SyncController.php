<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\SyncHistory;
use App\Services\PlatformSyncService;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(private PlatformSyncService $syncService) {}

    /**
     * Trigger order sync for a shop (queued).
     */
    public function syncOrders(Request $request, int $shopId)
    {
        $shop = Shop::findOrFail($shopId);

        try {
            // Run synchronously (like inventory) and return count
            $count = $this->syncService->syncOrdersNow($shop);

            return redirect()->back()
                ->with('success', "✓ Orders synced: {$count} records updated.");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * INVENTORY LEGACY ACTION: candidate for deletion when inventory sync is removed.
     */
    // public function syncInventory(Request $request, int $shopId)
    // {
    //     $shop = Shop::findOrFail($shopId);

    //     try {
    //         $count = $this->syncService->syncInventoryNow($shop);

    //         return redirect()->back()
    //             ->with('success', "✓ Inventory synced: {$count} records updated.");
    //     } catch (\Throwable $e) {
    //         return redirect()->back()
    //             ->with('error', 'Sync failed: ' . $e->getMessage());
    //     }
    // }

    /**
     * Show sync history list.
     */
    public function history(Request $request)
    {
        $histories = SyncHistory::with(['shop', 'platform'])
            ->orderByDesc('started_at')
            ->paginate(20)
            ->withQueryString();

        return view('btoc.sync.history', compact('histories'));
    }

    /**
     * Show detail of one sync history record.
     */
    public function historyDetail(int $id)
    {
        $history = SyncHistory::with(['shop', 'platform'])->findOrFail($id);

        return view('btoc.sync.detail', compact('history'));
    }
}
