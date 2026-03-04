<?php

namespace App\Services\Btoc;

use App\DTO\Btoc\RegisterTrackingDTO;
use App\Repositories\Btoc\OrderRepository;
use App\Services\Btoc\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OrderService
{
    protected OrderRepository $orderRepository;
    protected MailService $mailService;

    public function __construct(
        OrderRepository $orderRepository,
        MailService $mailService
    ) {
        $this->orderRepository = $orderRepository;
        $this->mailService = $mailService;
    }

    public function registerTracking(RegisterTrackingDTO $dto): void
    {
         dd('SERVICE_RUNNING');
        DB::transaction(function () use ($dto) {

            // 1️⃣ Lock row chống double submit
            $order = $this->orderRepository->findForUpdate($dto->orderId);

            if (!$order) {
                throw new RuntimeException('Order not found.');
            }

            // 2️⃣ Guard: không cho overwrite nếu đã shipped
            if ($order->status === 'shipped') {

                Log::warning('TRACKING_REGISTER_BLOCKED_ALREADY_SHIPPED', [
                    'order_id' => $order->id,
                    'current_tracking' => $order->tracking_number,
                    'new_tracking' => $dto->trackingNumber,
                ]);

                throw new RuntimeException('Order already shipped.');
            }

            // 3️⃣ Save tracking (qua repository)
            $updatedOrder = $this->orderRepository->saveTracking(
                $dto->orderId,
                $dto->trackingNumber
            );

            // 4️⃣ Audit log
            Log::info('TRACKING_REGISTERED', [
                'order_id' => $updatedOrder->id,
                'tracking_number' => $dto->trackingNumber,
                'status' => $updatedOrder->status,
                'shipped_at' => optional($updatedOrder->shipped_at)->toDateTimeString(),
            ]);

            // 5️⃣ Send mail (chỉ gửi khi transaction thành công)
            $this->mailService->sendTrackingMail($updatedOrder);
        });
    }
}