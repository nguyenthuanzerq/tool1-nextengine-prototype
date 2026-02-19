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

class NextEngineOrderService
{
    public function __construct(
        private OrderToNextEngineOrderBaseMapper $orderMapper,
        private NextEngineCsvBuilder $csvBuilder
    ) {}

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
        $connection = NextEngineConnection::latest('access_token_end_date')->first();

        if (!$connection) {
            return ['error' => 'NextEngineConnection not found'];
        }

        $response = Http::asForm()->post(
            config('services.next_engine.api_uri') . '/api_v1_receiveorder_base/upload',
            [
                'access_token'  => $connection->access_token,
                'refresh_token' => $connection->refresh_token,
                'wait_flag'     => 1,
                'receive_order_upload_pattern_id' => 2,
                'data_type_1' => 'csv',
                'data_1' => $data,
            ]
        );

        return $response->json() ?? [];
    }

    public function uploadQueueSearch(string $que_id): array
    {
        $connection = NextEngineConnection::latest('access_token_end_date')->first();

        if (!$connection) {
            return ['error' => 'NextEngineConnection not found'];
        }

        $response = Http::asForm()->post(
            config('services.next_engine.api_uri') . '/api_v1_system_que/search',
            [
                'access_token'  => $connection->access_token,
                'refresh_token' => $connection->refresh_token,
                'wait_flag'     => 1,
                'fields' => 'que_id,que_method_name,que_shop_id,que_upload_name,que_file_name,que_message,que_creation_date,que_creator_name,que_status_id',
                'que_id-eq' => $que_id,
            ]
        );

        //Them code de test
        Log::info('QUEUE RESPONSE', [
    'que_id' => $que_id,
    'json' => $response->json(),
    'status' => $response->status()
]);

        return $response->json() ?? [];
    }

    public function orderSlipSearch(int $receive_order_shop_cut_form_id): array
    {
        $connection = NextEngineConnection::latest('access_token_end_date')->first();

        if (!$connection) {
            return ['error' => 'NextEngineConnection not found'];
        }

        $response = Http::asForm()->post(
            config('services.next_engine.api_uri') . '/api_v1_receiveorder_base/search',
            [
                'access_token'  => $connection->access_token,
                'refresh_token' => $connection->refresh_token,
                'wait_flag'     => 1,
                'fields' => 'receive_order_id,receive_order_shop_id,receive_order_shop_cut_form_id,receive_order_import_date,receive_order_order_status_id,receive_order_total_amount',
                'receive_order_shop_cut_form_id-eq' => $receive_order_shop_cut_form_id,
            ]
        );

        return $response->json() ?? [];
    }

    public function uploadAndWait(Order $order): array
{
    // 1. Prepare CSV
    $prepared = $this->prepare($order);
    $csvData = $prepared->raw_response;
    
    //Them code de test
dd($csvData);

    if (empty($csvData)) {
        return ['error' => 'CSV empty'];
    }

    // 2. Upload
    $uploadResult = $this->uploadSalesOrder($csvData);

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

        $queue = $this->uploadQueueSearch($queId);

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
