<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Services\NextEngine\NextEngineOrderService;

class OrderController extends Controller
{
    public function __construct(
        private NextEngineOrderService $nextEngineOrderService
    ) {}

    public function syncNextEngine(Request $request)
    {
        $order = Order::with('nextEngineOrder')->findOrFail($request->order_id);
        $nextEngineOrder = $order->nextEngineOrder;

        // ==========================
        // FLOW A: Already uploaded
        // ==========================
        if ($nextEngineOrder && $nextEngineOrder->system_que_id) {

            if ($nextEngineOrder->system_que_status_id == 3) {

            // Hardcode tam --> de test --> Khi product se sua lai 
                // $orderSlipResponse = $this->nextEngineOrderService
                //     ->orderSlipSearch($nextEngineOrder->receive_order_shop_cut_form_id);
// TEST ONLY – khi chưa có dữ liệu thật từ NextEngine
$receiveOrderId = $nextEngineOrder->receive_order_shop_cut_form_id ?? 1;
$orderSlipResponse = $this->nextEngineOrderService
->orderSlipSearch((int) $receiveOrderId);

                $orderSlip = $orderSlipResponse['data'][0] ?? null;

                $nextEngineOrder->update([
                    'receive_order_order_status_id' =>
                        $orderSlip['receive_order_order_status_id'] ?? null,
                ]);

                return response([
                    'status' => 'success',
                    'message' => 'Order already synced successfully',
                ]);
            }

            $queueResponse = $this->nextEngineOrderService
                ->uploadQueueSearch($nextEngineOrder->system_que_id);

            $queue = $queueResponse['data'][0] ?? null;

            $nextEngineOrder->update([
                'system_que_response' => $queue,
                'system_que_status_id' => $queue['que_status_id'] ?? null
            ]);

            if (($queue['que_status_id'] ?? null) == 4) {
    return response([
        'status' => 'error',
        'message' => 'NextEngine upload failed',
        'queue' => $queue
    ], 400);
}
// sua de test
            // return $this->buildQueueResponse(
            //     $queue,
            //     ($queue['que_status_id'] ?? null) == 3
            //         ? 'Order already synced successfully'
            //         : 'Order is processing in NextEngine'
            // );

           return response()->json([
    'status' => ($queue['result'] ?? null) === 'error'
        ? 'error'
        : (($queue['que_status_id'] ?? null) == 3 ? 'success' : 'processing'),

    'message' => ($queue['result'] ?? null) === 'error'
        ? 'NextEngine queue error'
        : (($queue['que_status_id'] ?? null) == 3
            ? 'Order already synced successfully'
            : 'Order is processing in NextEngine'),

    'queue' => $queue
]);


        }

        // ==========================
// FLOW B: First upload
// ==========================

$nextEngineOrder = $this->nextEngineOrderService->prepare($order);

if (!$nextEngineOrder->raw_response) {
    return response([
        'status' => 'error',
        'message' => 'Payload CSV is empty',
    ], 400);
}

$uploadResponse = $this->nextEngineOrderService
    ->uploadSalesOrder($nextEngineOrder->raw_response);

if (!isset($uploadResponse['que_id'])) {
    return response([
        'status' => 'error',
        'message' => 'Upload failed',
        'response' => $uploadResponse
    ], 400);
}

$nextEngineOrder->update([
    'response' => $uploadResponse,
    'system_que_id' => $uploadResponse['que_id'],
    'system_que_status_id' => 1, // pending
    'last_synced_at' => now(),
]);

return response([
    'status' => 'success',
    'message' => 'Upload order successfully',
    'system_que_id' => $uploadResponse['que_id'],
]);

    }
}
