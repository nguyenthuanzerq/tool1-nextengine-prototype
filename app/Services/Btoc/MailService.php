<?php

namespace App\Services\Btoc;

use App\Mail\OrderCreatedMail;
use App\Models\NextEngineOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function sendOrderCreatedMail(NextEngineOrder $order): void
    {
        try {
            $customerEmail = 'khoa.trandang020704@hcmut.edu.vn';

            if ($customerEmail) {
                Mail::to($customerEmail)->send(new OrderCreatedMail($order));
                Log::info('Đã gửi email xác nhận đặt hàng', ['order_id' => $order->id]);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi gửi email xác nhận đặt hàng', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
