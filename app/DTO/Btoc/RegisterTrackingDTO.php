<?php

namespace App\DTO\Btoc;

class RegisterTrackingDTO
{
    public int $orderId;
    public string $trackingNumber;

    public function __construct(int $orderId, string $trackingNumber)
    {
        $this->orderId = $orderId;
        $this->trackingNumber = $trackingNumber;
    }
}
