<?php

namespace App\Http\Controllers\Btoc;

use App\Http\Controllers\Controller;
use App\Mail\ShipmentNotificationMail;
use App\Models\PlatformConnection;
use App\Models\PlatformOrder;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PlatformOrder::with(['shop', 'platform']);

        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->filled('platform_id')) {
            $query->where('platform_id', $request->platform_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('ordered_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('ordered_at', '<=', $request->date_to);
        }

        if ($request->filled('sync_status')) {
            $query->where('sync_status', $request->string('sync_status'));
        }

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('platform_order_id', 'like', "%{$keyword}%")
                    ->orWhere('buyer_name', 'like', "%{$keyword}%");
            });
        }

        $orders = $query->orderByDesc('ordered_at')->paginate(20)->withQueryString();
        $shops  = Shop::whereHas('platform', function ($query) {
            $query->whereIn('key', ['nextengine', 'yahoo', 'rakuten', 'shopify']);
        })->orderBy('shop_name')->get();

        return view('btoc.orders.index', [
            'orders'  => $orders,
            'shops'   => $shops,
            'filters' => $request->only(['shop_id', 'platform_id', 'date_from', 'date_to', 'keyword', 'sync_status']),
        ]);
    }

    public function show($id)
    {
        $order = PlatformOrder::with(['shop', 'platform', 'items'])->findOrFail($id);

        return view('btoc.orders.detail', ['order' => $order]);
    }

    public function update(Request $request, $id)
    {
        $order = PlatformOrder::with('platform')->findOrFail($id);

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);

        $platformKey = strtolower((string) optional($order->platform)->key);

        // Only sync with NextEngine orders; other platforms update local tracking only.
        if ($platformKey === 'nextengine') {
            $connection = PlatformConnection::query()
                ->where('platform_id', $order->platform_id)
                ->where('shop_id', $order->shop_id)
                ->first();

            $accessToken = $connection?->access_token;
            $refreshToken = $connection?->refresh_token;

            if (empty($accessToken) || empty($refreshToken)) {
                return $this->updateFailedResponse($request, 'このショップのNextEngineトークンが未設定です。/ NextEngine token is not configured for this shop.');
            }

            $baseUrl = 'https://api.next-engine.org/api_v1_receiveorder_base';
            $receiveOrderId = (string) ($order->platform_order_id ?: $order->id);

            $httpClient = Http::asForm()->timeout(20);
            // Local Testing
            // if (app()->isLocal()) {
            //     $httpClient = $httpClient->withoutVerifying();
            // }

            // Call API to get receive_order_last_modified_date value in Next Engine
            try {
                $searchResponse = $httpClient->post("$baseUrl/search", [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'receive_order_id-eq' => $receiveOrderId,
                    'fields' => 'receive_order_last_modified_date',
                ]);
            } catch (\Throwable $e) {
                Log::error('NextEngine search request failed with exception.', [
                    'order_id' => $order->id,
                    'platform_order_id' => $order->platform_order_id,
                    'error' => $e->getMessage(),
                ]);

                return $this->updateFailedResponse($request, 'NextEngineへの接続に失敗しました（SSL/接続エラー）。/ Could not connect to NextEngine (SSL/connection error).');
            }

            //Error Handing
            if (! $searchResponse->successful() || $searchResponse->json('result') !== 'success') {
                Log::warning('NextEngine search failed while updating tracking number.', [
                    'order_id' => $order->id,
                    'platform_order_id' => $order->platform_order_id,
                    'response' => $searchResponse->json(),
                ]);

                return $this->updateFailedResponse($request, 'NextEngineから受注情報の取得に失敗しました。/ Failed to fetch order information from NextEngine.');
            }
            
            //Set XML data
            $lastModifiedDate = $searchResponse->json('data.0.receive_order_last_modified_date');
            if (! $lastModifiedDate) {
                return $this->updateFailedResponse($request, 'NextEngineの最終更新日時を取得できませんでした。/ Could not get receive_order_last_modified_date from NextEngine.');
            }

            $trackingNumberXml = htmlspecialchars(
                $validated['tracking_number'],
                ENT_XML1 | ENT_COMPAT,
                'UTF-8'
            );

            $xmlData = '<?xml version="1.0" encoding="utf-8"?><root><receiveorder_base><receive_order_delivery_cut_form_id>'
                . $trackingNumberXml
                . '</receive_order_delivery_cut_form_id></receiveorder_base></root>';
            // Call API to update tracking number
            try {
                $updateResponse = $httpClient->post("$baseUrl/update", [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'receive_order_id' => $receiveOrderId,
                    'receive_order_last_modified_date' => $lastModifiedDate,
                    'data' => $xmlData,
                    'receive_order_shipped_update_flag' => 1,
                    'wait_flag' => 1,
                ]);
            } catch (\Throwable $e) {
                Log::error('NextEngine update request failed with exception.', [
                    'order_id' => $order->id,
                    'platform_order_id' => $order->platform_order_id,
                    'error' => $e->getMessage(),
                ]);

                return $this->updateFailedResponse($request, 'NextEngineの更新に失敗しました（SSL/接続エラー）。/ Could not update NextEngine (SSL/connection error).');
            }

            $result = $updateResponse->json();
            // Error Handing
            if (! $updateResponse->successful() || ($result['result'] ?? null) !== 'success') {
                Log::warning('NextEngine update failed while updating tracking number.', [
                    'order_id' => $order->id,
                    'platform_order_id' => $order->platform_order_id,
                    'response' => $result,
                ]);

                return $this->updateFailedResponse($request, 'NextEngine更新エラー: / NextEngine update error: ' . ($result['message'] ?? 'Unknown error'));
            }
        } else {
            return $this->updateFailedResponse($request, 'Only NextEngine orders support tracking updates in the current scope.');
        }

        // Update tracking number in DB
        $order->update([
            'tracking_number' => $validated['tracking_number'],
        ]);

        $notifyEmail = config('mail.notification_email');
        if ($notifyEmail) {
            $user = auth()->user();
            $mailerName = 'smtp'; // default to system smtp

            if ($user && $user->email_smtp && $user->app_password) {
                config([
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.host' => 'smtp.gmail.com',
                    'mail.mailers.smtp.port' => 587,
                    'mail.mailers.smtp.encryption' => 'tls',
                    'mail.mailers.smtp.username' => $user->email_smtp,
                    'mail.mailers.smtp.password' => $user->app_password,
                    'mail.from.address' => $user->email_smtp,
                    'mail.from.name' => config('app.name'),
                ]);
                app('mail.manager')->purge('smtp'); // Clear cached transporter
            }

            try {
                Mail::mailer($mailerName)->to($notifyEmail)->send(new ShipmentNotificationMail($order->load('shop')));
            } catch (\Throwable $e) {
                Log::error('Gửi mail thất bại: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => '追跡番号を保存しました。']);
        }

        return redirect()->route('btoc.orders.index')->with('success', '注文が正常に更新されました。');
    }

    private function updateFailedResponse(Request $request, string $message)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()->back()->withInput()->with('error', $message);
    }

    public function destroy($id)
    {
        $order = PlatformOrder::with('platform')->findOrFail($id);

        if ($order->platform?->key !== 'nextengine') {
            return response()->json(['success' => false, 'message' => 'Only NextEngine orders are enabled.'], 422);
        }

        if (in_array($order->sync_status, [PlatformOrder::STATUS_PENDING, PlatformOrder::STATUS_FAILED], true)) {
            $order->update(['sync_status' => PlatformOrder::STATUS_IGNORED]);
        }

        return response()->json(['success' => true, 'sync_status' => $order->fresh()->sync_status]);
    }
}
