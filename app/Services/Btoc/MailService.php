<?php

namespace App\Services\Btoc;
use Illuminate\Support\Facades\Log;

use App\Models\Order;

class MailService
{
    public function sendTrackingMail(Order $order): void
{
    Log::info('BTOC_TRACKING_MAIL_TRIGGERED', [
        'timestamp'       => now()->toDateTimeString(),
        'order_id'        => $order->id,
        'tracking_number' => $order->tracking_number,
        'customer_email'  => $order->email ?? null,
        'shop_id'         => $order->shop_id ?? null,
        'environment'     => app()->environment(),
        'ip'              => request()->ip(),
        'user_agent'      => request()->userAgent(),
    ]);
}

}
