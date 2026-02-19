<?php

namespace App\Services\Btoc;

use App\DTO\Btoc\RegisterTrackingDTO;
use App\Repositories\Btoc\OrderRepository;
use App\Services\Btoc\MailService;

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
        $order = $this->orderRepository->saveTracking(
            $dto->orderId,
            $dto->trackingNumber
        );

        $this->mailService->sendTrackingMail($order);
    }
}
