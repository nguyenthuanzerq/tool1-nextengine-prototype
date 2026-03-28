<?php

namespace App\Mail;

use App\Models\PlatformOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShipmentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly PlatformOrder $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【出荷通知】注文番号: ' . $this->order->platform_order_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.shipment_notification',
        );
    }
}
