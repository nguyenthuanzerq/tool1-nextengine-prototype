<?php

namespace App\Repositories\Btoc;

use App\Models\Order;

class OrderRepository
{
    /**
     * Lock order row để chống double submit
     */
    public function findForUpdate(int $orderId): ?Order
    {
        return Order::where('id', $orderId)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Lưu tracking + cập nhật trạng thái shipped
     */
    public function saveTracking(int $orderId, string $trackingNumber): Order
    {
        $order = Order::findOrFail($orderId);

        $order->tracking_number = $trackingNumber;
        $order->status = 'shipped';
        $order->shipped_at = now();
        $order->save();

        return $order;
    }
}
