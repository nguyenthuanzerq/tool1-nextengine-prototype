<?php

namespace App\Repositories\Btoc;

use App\Models\Order;

class OrderRepository
{
    public function saveTracking(int $orderId, string $trackingNumber): Order
    {
        $order = Order::findOrFail($orderId);

        $order->tracking_number = $trackingNumber;
        $order->save();

        return $order;
    }
}
