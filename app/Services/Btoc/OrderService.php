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
        // Đã xóa hàm dd() cản trở luồng chạy

        DB::transaction(function () use ($dto) {

            // 1. Lock row chống double submit
            $order = $this->orderRepository->findForUpdate($dto->orderId);

            if (!$order) {
                throw new RuntimeException('Order not found.');
            }

            // 2. Guard: Không cho overwrite nếu đã shipped (Thêm check tiếng Nhật)
            if ($order->status === 'shipped' || $order->status === '出荷済み') {
                Log::warning('TRACKING_REGISTER_BLOCKED_ALREADY_SHIPPED', [
                    'order_id' => $order->id,
                    'current_tracking' => $order->tracking_number,
                    'new_tracking' => $dto->trackingNumber,
                ]);

                throw new RuntimeException('Order already shipped.');
            }

            // 3. Save tracking
            $updatedOrder = $this->orderRepository->saveTracking(
                $dto->orderId,
                $dto->trackingNumber
            );

            // 4. Audit log
            Log::info('TRACKING_REGISTERED', [
                'order_id' => $updatedOrder->id,
                'tracking_number' => $dto->trackingNumber,
                'status' => $updatedOrder->status,
                'shipped_at' => optional($updatedOrder->shipped_at)->toDateTimeString(),
            ]);

            // 5. Send mail
            $this->mailService->sendTrackingMail($updatedOrder);
        });
    }
}