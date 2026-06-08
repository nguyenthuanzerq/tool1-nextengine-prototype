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

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('platform_order_id', 'like', "%{$keyword}%")
                    ->orWhere('buyer_name', 'like', "%{$keyword}%");
            });
        }

        $orders = $query->orderByDesc('ordered_at')->paginate(20)->withQueryString();
        $shops  = Shop::orderBy('shop_name')->get();

        return view('btoc.orders.index', [
            'orders'  => $orders,
            'shops'   => $shops,
            'filters' => $request->only(['shop_id', 'platform_id', 'date_from', 'date_to', 'keyword']),
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
        } elseif ($platformKey === 'yahoo') {
            $connection = PlatformConnection::query()
                ->where('platform_id', $order->platform_id)
                ->where('shop_id', $order->shop_id)
                ->first();

            if (!$connection) {
                return $this->updateFailedResponse($request, 'このショップのYahoo Shopping認証情報が未設定です。/ Yahoo Shopping credentials not found.');
            }

            // Ensure access token is refreshed
            try {
                $connector = app(\App\Connectors\YahooConnector::class);
                $connector->refreshTokenIfNeeded($connection);
            } catch (\Throwable $e) {
                return $this->updateFailedResponse($request, 'Yahoo Shoppingアクセストークンの更新に失敗しました: ' . $e->getMessage());
            }

            $accessToken = $connection->access_token;
            if (empty($accessToken)) {
                return $this->updateFailedResponse($request, 'Yahoo Shoppingトークンが未設定です。/ Yahoo Shopping token is not configured.');
            }

            $trackingNumberXml = htmlspecialchars(
                $validated['tracking_number'],
                ENT_XML1 | ENT_COMPAT,
                'UTF-8'
            );

            $xmlData = '<?xml version="1.0" encoding="UTF-8"?>' .
                '<Req>' .
                '    <Target>' .
                '        <OrderId>' . htmlspecialchars($order->platform_order_id, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</OrderId>' .
                '        <IsPointFix>true</IsPointFix>' .
                '        <OperationUser>System</OperationUser>' .
                '    </Target>' .
                '    <Order>' .
                '        <Ship>' .
                '            <ShipStatus>3</ShipStatus>' . // 3 = Shipped
                '            <ShipInvoiceNumber1>' . $trackingNumberXml . '</ShipInvoiceNumber1>' .
                '        </Ship>' .
                '    </Order>' .
                '    <SellerId>' . htmlspecialchars($connection->seller_id, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</SellerId>' .
                '</Req>';

            try {
                $response = Http::withToken($accessToken)
                    ->withHeaders(['Content-Type' => 'application/xml'])
                    ->post('https://circus.shopping.yahooapis.jp/ShoppingWebService/V1/orderShipStatusChange', $xmlData);
            } catch (\Throwable $e) {
                Log::error('Yahoo orderShipStatusChange request failed', [
                    'order_id' => $order->id,
                    'platform_order_id' => $order->platform_order_id,
                    'error' => $e->getMessage(),
                ]);
                return $this->updateFailedResponse($request, 'Yahoo Shoppingへの接続に失敗しました。/ Could not connect to Yahoo Shopping.');
            }

            if (!$response->successful()) {
                Log::warning('Yahoo orderShipStatusChange failed', [
                    'order_id' => $order->id,
                    'platform_order_id' => $order->platform_order_id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->updateFailedResponse($request, 'Yahoo Shopping更新エラー: HTTP ' . $response->status());
            }

            try {
                $xml = simplexml_load_string($response->body());
                if ($xml && $xml->getName() === 'Error') {
                    return $this->updateFailedResponse($request, 'Yahoo Shoppingエラー: ' . (string) $xml->Message . ' (' . (string) $xml->Code . ')');
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to parse Yahoo response XML', [
                    'body' => $response->body(),
                    'error' => $e->getMessage(),
                ]);
            }
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

            Mail::mailer($mailerName)->to($notifyEmail)->send(new ShipmentNotificationMail($order->load('shop')));
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
        PlatformOrder::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
