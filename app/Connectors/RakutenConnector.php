<?php

namespace App\Connectors;

use App\Contracts\ApiKeyConnector;
use App\Models\PlatformConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Exception;

class RakutenConnector implements ApiKeyConnector
{
    public function authType(): string
    {
        return 'api_key';
    }

    public function buildAuthHeaders(PlatformConnection $conn): array
    {
        // Rakuten uses Base64(serviceSecret:licenseKey) Basic auth
        $encoded = base64_encode($conn->client_id . ':' . $conn->client_secret);
        return [
            'Authorization' => 'ESA ' . $encoded,
            'Content-Type' => 'application/json; charset=utf-8'
        ];
    }

    private function sendRequest(PlatformConnection $conn, string $url, array $body)
    {
        $headers = $this->buildAuthHeaders($conn);
        $response = Http::withoutVerifying()->withHeaders($headers)->post($url, $body);

        if (!$response->successful()) {
            throw new Exception("Rakuten API Error: " . $response->body());
        }

        return $response->json();
    }

    public function testConnection(PlatformConnection $conn): bool
    {
        $url = 'https://api.rms.rakuten.co.jp/es/2.0/order/searchOrder/';
        $body = [
            'orderProgressList' => [100],
            'dateType' => 1,
            'startDatetime' => now()->setTimezone('Asia/Tokyo')->subDay()->format('Y-m-d\TH:i:sO'),
            'endDatetime' => now()->setTimezone('Asia/Tokyo')->format('Y-m-d\TH:i:sO'),
            'PaginationRequestModel' => [
                'requestRecordsAmount' => 1,
                'requestPage' => 1
            ]
        ];
        $this->sendRequest($conn, $url, $body);
        return true;
    }

    public function fetchOrders(PlatformConnection $conn, array $opts = []): iterable
    {
        $url = 'https://api.rms.rakuten.co.jp/es/2.0/order/searchOrder/';
        $startDatetime = $opts['startDatetime'] ?? now()->setTimezone('Asia/Tokyo')->subDays(7)->format('Y-m-d\TH:i:sO');
        $endDatetime = $opts['endDatetime'] ?? now()->setTimezone('Asia/Tokyo')->format('Y-m-d\TH:i:sO');

        $body = [
            'orderProgressList' => [100, 200, 300, 400, 500, 600, 700, 800, 900], // Adjust based on needs
            'dateType' => 1,
            'startDatetime' => $startDatetime,
            'endDatetime' => $endDatetime,
            'PaginationRequestModel' => [
                'requestRecordsAmount' => 50,
                'requestPage' => 1
            ]
        ];

        $orderNumberList = [];
        try {
            $response = $this->sendRequest($conn, $url, $body);
            $orderNumberList = $response['orderNumberList'] ?? [];
        } catch (\Throwable $e) {
            throw $e;
        }

        if (empty($orderNumberList)) {
            return [];
        }

        // Fetch detailed orders
        $detailsUrl = 'https://api.rms.rakuten.co.jp/es/2.0/order/getOrder/';
        $chunks = array_chunk($orderNumberList, 50);
        $orders = [];

        foreach ($chunks as $chunk) {
            $detailsBody = [
                'orderNumberList' => $chunk,
                'version' => 7
            ];
            $res = $this->sendRequest($conn, $detailsUrl, $detailsBody);
            
            $fetched = $res['OrderModelList'] ?? [];
            $orders = array_merge($orders, $fetched);
        }

        return $orders;
    }

    public function fetchOrderItems(PlatformConnection $conn, array $orderIds): iterable
    {
        $detailsUrl = 'https://api.rms.rakuten.co.jp/es/2.0/order/getOrder/';
        $chunks = array_chunk($orderIds, 50);
        $items = [];

        foreach ($chunks as $chunk) {
            $detailsBody = [
                'orderNumberList' => $chunk,
                'version' => 7
            ];
            $res = $this->sendRequest($conn, $detailsUrl, $detailsBody);
            
            $fetchedOrders = $res['OrderModelList'] ?? [];
            foreach ($fetchedOrders as $order) {
                $orderNumber = $order['orderNumber'] ?? null;
                $packageList = $order['PackageModelList'] ?? []; 
                
                foreach ($packageList as $package) {
                    $itemList = $package['ItemModelList'] ?? [];
                    foreach ($itemList as $item) {
                        $item['orderNumber'] = $orderNumber; // inject for normalization
                        $items[] = $item;
                    }
                }
            }
        }

        return $items;
    }

    public function updateShipment(PlatformConnection $conn, string $orderId, array $trackingData): void
    {
        // 1. Fetch current order to get basketId (and shippingDetailId to update instead of add)
        $getOrderUrl = 'https://api.rms.rakuten.co.jp/es/2.0/order/getOrder/';
        $getBody = [
            'orderNumberList' => [$orderId],
            'version' => 7
        ];
        $getOrderRes = $this->sendRequest($conn, $getOrderUrl, $getBody);
        
        $orderInfo = $getOrderRes['OrderModelList'][0] ?? null;
        if (!$orderInfo) {
            throw new \Exception("Cannot find order {$orderId} to update shipping");
        }

        $basketId = $orderInfo['PackageModelList'][0]['basketId'] ?? null;
        $shippingDetailId = $orderInfo['PackageModelList'][0]['ShippingModelList'][0]['shippingDetailId'] ?? null;

        if (!$basketId) {
            throw new \Exception("Cannot find basketId for order {$orderId}");
        }

        // Map common carriers to Rakuten deliveryCompany codes
        // 1000: Other, 1001: Yamato, 1002: Sagawa, 1003: Japan Post, 1004: Seino
        $carrierMap = [
            'yamato'    => '1001',
            'sagawa'    => '1002',
            'japanpost' => '1003',
            'seino'     => '1004',
        ];
        
        $carrierInput = strtolower($trackingData['carrier'] ?? '');
        $carrierCode = $carrierMap[$carrierInput] ?? '1000';

        $shippingModel = [
            'deliveryCompany' => $carrierCode,
            'shippingNumber'  => (string) $trackingData['tracking_number'],
            'shippingDate'    => $trackingData['shipped_at'] ?? now()->format('Y-m-d')
        ];
        
        if ($shippingDetailId) {
            $shippingModel['shippingDetailId'] = $shippingDetailId;
        }

        // 2. Update shipping
        $updateUrl = 'https://api.rms.rakuten.co.jp/es/2.0/order/updateOrderShipping/';
        $body = [
            'orderNumber' => $orderId,
            'BasketidModelList' => [
                [
                    'basketId' => $basketId,
                    'ShippingModelList' => [ $shippingModel ]
                ]
            ]
        ];

        $this->sendRequest($conn, $updateUrl, $body);
    }

    public function fetchInventory(PlatformConnection $conn, array $opts = []): iterable
    {
        return [];
    }

    public function webhookHandler(Request $request): void {}

    public function normalizeOrderItem(array $raw): array
    {
        return [
            'platform_item_id' => $raw['itemDetailId'] ?? $raw['detailNo'] ?? null,
            'product_code'     => $raw['manageNumber'] ?? null,
            'product_name'     => $raw['itemName'] ?? null,
            'quantity'         => (int) ($raw['units'] ?? 1),
            'unit_price'       => (float) ($raw['price'] ?? 0),
            'total_price'      => (float) (($raw['price'] ?? 0) * ($raw['units'] ?? 1)),
            'orderNumber'      => $raw['orderNumber'] ?? null, // for PlatformSyncService to map it back
        ];
    }

    public function normalizeOrder(array $raw): array
    {
        $orderer = $raw['OrdererModel'] ?? [];
        $settlement = $raw['SettlementModel'] ?? [];
        $delivery = $raw['DeliveryModel'] ?? [];
        $package = $raw['PackageModelList'][0] ?? [];
        $sender = $package['SenderModel'] ?? [];
        $shipping = $package['ShippingModelList'][0] ?? [];

        return [
            'platform_order_id'     => $raw['orderNumber'] ?? null,
            'platform_order_status' => (string) ($raw['orderProgress'] ?? ''),
            'ordered_at'            => $raw['orderDatetime'] ?? null,
            
            'buyer_name'            => trim(($orderer['familyName'] ?? '') . ' ' . ($orderer['firstName'] ?? '')),
            'buyer_email'           => $orderer['emailAddress'] ?? null,
            'buyer_phone'           => ($orderer['phoneNumber1'] ?? '') . ($orderer['phoneNumber2'] ?? '') . ($orderer['phoneNumber3'] ?? ''),
            'buyer_zip'             => ($orderer['zipCode1'] ?? '') . '-' . ($orderer['zipCode2'] ?? ''),
            'buyer_address'         => trim(($orderer['prefecture'] ?? '') . ' ' . ($orderer['city'] ?? '') . ' ' . ($orderer['subAddress'] ?? '')),
            
            'delivery_name'         => trim(($sender['familyName'] ?? '') . ' ' . ($sender['firstName'] ?? '')),
            'delivery_zip'          => ($sender['zipCode1'] ?? '') . '-' . ($sender['zipCode2'] ?? ''),
            'delivery_address'      => trim(($sender['prefecture'] ?? '') . ' ' . ($sender['city'] ?? '') . ' ' . ($sender['subAddress'] ?? '')),
            
            'delivery_method'       => $delivery['deliveryName'] ?? null,
            'payment_method'        => $settlement['settlementMethod'] ?? null,
            
            'goods_amount'          => $raw['goodsPrice'] ?? null,
            'delivery_fee'          => $raw['postagePrice'] ?? null,
            'total_amount'          => $raw['requestPrice'] ?? null,

            'tracking_number'       => $shipping['shippingNumber'] ?? null,
            'shipped_at'            => $shipping['shippingDate'] ?? null,
        ];
    }

    public function normalizeInventory(array $raw): array
    {
        return [
            'product_code' => $raw['manageNumber'] ?? null,
            'product_name' => $raw['itemName'] ?? null,
            'stock'        => $raw['inventoryCount'] ?? 0,
        ];
    }
}
