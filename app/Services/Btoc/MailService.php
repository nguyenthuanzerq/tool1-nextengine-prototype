<?php

namespace App\Services\Btoc;

use App\Mail\OrderCreatedMail;
use Illuminate\Support\Facades\Log;

use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class MailService
{
    public function sendOrderCreatedMail(Order $order): void
    {
        try {
            // LƯU Ý: Bạn cần trỏ đúng vào trường lưu email khách hàng trong DB của bạn. 
            // Nếu lưu trong JSON address thì parse ra, hoặc trỏ thẳng vào cột email
            $customerEmail = 'trandangkhoa020704@gmail.com'; // Tạm thời hardcode email của bạn để test, sau này thay bằng email thật của đơn hàng: $order->purchaser_email
            
            if ($customerEmail) {
                Mail::to($customerEmail)->send(new OrderCreatedMail($order));
                Log::info('Đã gửi email xác nhận đặt hàng', ['order_id' => $order->id]);
            }
        } catch (\Exception $e) {
            Log::error('Lỗi khi gửi email xác nhận đặt hàng', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
