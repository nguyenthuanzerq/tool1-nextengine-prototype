<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'receipt_receipt_id',
        'receive_order_shop_cut_form_id',
        'receive_order_date',
        'receive_order_total_amount',
        'purchaser_name',
        'purchaser_id',
        'shipping_address',
        'purchaser_phone',
        'purchaser_email',
        'receive_order_order_status_id',
        'shipping_delivery_tracking_number',
        'carrier_name',
        'status',
        'shipped_at',
    ];

    // Auto cast data type
    protected $casts = [
        'receive_order_date' => 'datetime',
        'receive_order_import_date' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    /**
     * Relationship: One order belongs to one shop
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    /**
     * Relationship: Link to raw data from NextEngine
     */
    public function nextEngineOrder()
    {
        return $this->hasOne(NextEngineOrder::class, 'order_id', 'id');
    }
}
