<?php

namespace App\Services\NextEngine;

use App\Models\Order;
use App\Models\NextEngineOrder;
use App\Services\NextEngine\Mappers\OrderToNextEngineOrderBaseMapper;
use App\Services\NextEngine\Csv\NextEngineCsvBuilder;
use Illuminate\Support\Facades\Http;
use App\Models\NextEngineConnection;
// them de test
use Illuminate\Support\Facades\Log;
use App\Services\NextEngine\NextEngineClient;

class NextEngineOrderService
{
    public function __construct(
        private OrderToNextEngineOrderBaseMapper $orderMapper,
        private NextEngineCsvBuilder $csvBuilder,
         private NextEngineClient $client
    ) {
         $this->orderMapper = $orderMapper;
    $this->csvBuilder = $csvBuilder;
    $this->client = $client;
    }

    public function prepare(Order $order): NextEngineOrder
    {
        $rows = [];

        if ($order->relationLoaded('orderProducts') || method_exists($order, 'orderProducts')) {
            $products = $order->orderProducts ?? [];
        } else {
            $products = [];
        }

        if (!empty($products)) {
            foreach ($products as $item) {
                $rows[] = $this->orderMapper->map($order, $item);
            }
        }

        if (empty($rows)) {
            $payloadCsv = '';
        } else {
//→ Nếu thứ tự key trong array thay đổi -> → CSV format sẽ sai
//-> Production nên -> $headers = ['店舗伝票番号','受注日',...] -> Để khóa format.
            $headers = array_keys($rows[0]);
            $payloadCsv = $this->csvBuilder->build($headers, $rows);
        }

        return NextEngineOrder::updateOrCreate(
            ['order_id' => $order->id],
            [
                'next_engine_order_id' => $order->id,
                'raw_response' => $payloadCsv,
            ]
        );
    }

    public function uploadSalesOrder(string $data): array
{
    return $this->client->uploadSalesOrder($data);
}

public function uploadQueueSearch(string $queId): array
{
    return $this->client->uploadQueueSearch($queId);
}

public function orderSlipSearch(int $id): array
{
    return $this->client->orderSlipSearch($id);
}


    public function uploadAndWait(Order $order): array
{
    // 1. Prepare CSV
    $prepared = $this->prepare($order);
    $csvData = $prepared->raw_response;
    
    //Comment code de test
// dd($csvData);

    if (empty($csvData)) {
        return ['error' => 'CSV empty'];
    }

    // 2. Upload
    // Comment de Test
    // $uploadResult = $this->uploadSalesOrder($csvData);
    $uploadResult = $this->client->uploadSalesOrder($csvData);


    if (!isset($uploadResult['result']) || $uploadResult['result'] !== 'success'){
    return [
        'error' => 'Upload failed',
        'response' => $uploadResult
    ];
}

    $queId = $uploadResult['que_id'] ?? null;

if (!$queId) {
    return ['error' => 'Queue ID not found'];
}


    // 3. Poll queue (max 10 lần)
    for ($i = 0; $i < 10; $i++) {
        sleep(2);

        // Comment de Test
        // $queue = $this->uploadQueueSearch($queId);
        $queue = $this->client->uploadQueueSearch($queId);



        if (!empty($queue['data'][0]['que_status_id'])) {
    $status = $queue['data'][0]['que_status_id'];

    if ($status == 3) {   // success
        return [
            'success' => true,
            'que_id' => $queId,
            'queue' => $queue
        ];
    }

    if ($status == 4) {   // error
        return [
            'error' => 'Upload failed',
            'queue' => $queue
        ];
    }
}

    }

    return [
        'error' => 'Queue timeout',
        'que_id' => $queId
    ];
}

}
