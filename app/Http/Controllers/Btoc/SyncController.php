<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Models\PlatformOrder;
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
        $shop = Shop::with('platform')->findOrFail($shopId);

        try {
            // Run synchronously and return count
            $count = $this->syncService->syncOrdersNow($shop);

            return redirect()->back()
                ->with('success', "✓ Orders synced: {$count} records updated.");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Trigger the legacy inventory sync flow manually.
     */
    public function syncInventory(Request $request, int $shopId)
    {
        $shop = Shop::with('platform')->findOrFail($shopId);

        try {
            $count = $this->syncService->syncInventoryNow($shop);

            return redirect()->back()
                ->with('success', "Inventory synced: {$count} records updated.");
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Inventory sync failed: ' . $e->getMessage());
        }
    }

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

    /**
     * Retry failed orders for a shop based on a sync history record.
     */
    public function retryOrder(int $id)
    {
        $history = SyncHistory::with('platform')->findOrFail($id);

        if ($history->sync_type !== 'orders_push' || $history->platform?->key !== 'nextengine') {
            return redirect()->back()->with('error', 'Only failed NextEngine order pushes can be retried.');
        }

        $orders = PlatformOrder::where('shop_id', $history->shop_id)
            ->where('sync_status', PlatformOrder::STATUS_FAILED)
            ->where('platform_id', $history->platform_id)
            ->get();

        $orders->each->update(['sync_status' => PlatformOrder::STATUS_PENDING]);
        $processed = $this->syncService->pushPendingOrdersToNextEngine();

        return redirect()->back()->with('success', "Retry completed for {$processed} order(s).");
    }

    public function retrySingleOrder(int $id)
    {
        $order = PlatformOrder::with('platform')->findOrFail($id);

        if (! in_array($order->platform?->key, ['yahoo', 'rakuten', 'shopify'], true) || $order->sync_status !== PlatformOrder::STATUS_FAILED) {
            return redirect()->back()->with('error', 'Only failed marketplace orders can be retried.');
        }

        $order->update(['sync_status' => PlatformOrder::STATUS_PENDING]);
        $this->syncService->pushPendingOrdersToNextEngine();

        return redirect()->back()->with('success', 'Order retry completed.');
    }

    public function ignoreOrder(int $id)
    {
        $order = PlatformOrder::with('platform')->findOrFail($id);

        if (! in_array($order->platform?->key, ['yahoo', 'rakuten', 'shopify'], true) || ! in_array($order->sync_status, [PlatformOrder::STATUS_PENDING, PlatformOrder::STATUS_FAILED], true)) {
            return redirect()->back()->with('error', 'Only pending or failed marketplace orders can be ignored.');
        }

        $order->update(['sync_status' => PlatformOrder::STATUS_IGNORED]);

        return redirect()->back()->with('success', 'Order was removed from the sync queue.');
    }
}
