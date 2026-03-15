<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NextEngineOrder extends Model
{
    public const ORDER_STATUS_IMPORT_INFO_LACK      = 0;
    public const ORDER_STATUS_EMAIL_IMPORTED        = 1;
    public const ORDER_STATUS_CREATED               = 2;
    public const ORDER_STATUS_INVOICE_WAITING       = 20;
    public const ORDER_STATUS_INVOICE_PRINTING      = 30;
    public const ORDER_STATUS_INVOICE_PRINTED       = 40;
    public const ORDER_STATUS_SHIPMENT_COMPLETED    = 50;

    public function getReceiveOrderStatusLabelAttribute(): string
    {
        return match ((int) $this->receive_order_order_status_id) {
            self::ORDER_STATUS_IMPORT_INFO_LACK   => '取込情報不足',
            self::ORDER_STATUS_EMAIL_IMPORTED     => '受注メール取込済',
            self::ORDER_STATUS_CREATED            => '起票済(CSV/手入力)',
            self::ORDER_STATUS_INVOICE_WAITING    => '納品書印刷待ち',
            self::ORDER_STATUS_INVOICE_PRINTING   => '納品書印刷中',
            self::ORDER_STATUS_INVOICE_PRINTED    => '納品書印刷済',
            self::ORDER_STATUS_SHIPMENT_COMPLETED => '出荷確定済（完了）',
            default                               => '不明',
        };
    }

    protected $table = 'next_engine_orders';

    protected $fillable = [
        'shop_id',
        'receive_order_date',
        'receive_order_import_date',
        'receive_order_delivery_id',
        'receive_order_include_possible_order_id',
        'receive_order_customer_type_name',
        'receive_order_purchaser_address1',
        'receive_order_creator_name',
        'receive_order_delivery_fee_amount',
        'receive_order_goods_amount',
        'receive_order_purchaser_address2',
        'receive_order_payment_method_name',
        'receive_order_id',
        'receive_order_last_modified_date',
        'receive_order_confirm_check_id',
        'receive_order_confirm_ids',
        'receive_order_confirm_check_name',
        'receive_order_order_status_id',
        'tracking_number',
        'raw_response'
    ];

    protected $casts = [
        'receive_order_date' => 'datetime',
        'receive_order_import_date' => 'datetime',
        'receive_order_last_modified_date' => 'datetime',
        'receive_order_goods_amount' => 'decimal:2',
        'receive_order_delivery_fee_amount' => 'decimal:2',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
