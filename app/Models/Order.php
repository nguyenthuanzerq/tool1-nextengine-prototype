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
        'receive_order_order_status_id',
        'shipping_delivery_tracking_number',
        'status', 
        'shipped_at'
    ];

    // Tự động ép kiểu dữ liệu
    protected $casts = [
        'receive_order_date' => 'datetime',
        'receive_order_import_date' => 'datetime',
        'shipped_at' => 'datetime',
    ];

    /**
     * Quan hệ: Một đơn hàng thuộc về một Shop
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }

    /**
     * Quan hệ: Một đơn hàng có nhiều Sản phẩm (Order Products)
     */
    public function products()
    {
        return $this->hasMany(OrderProduct::class, 'order_id', 'id');
    }

    /**
     * Quan hệ: Liên kết với dữ liệu thô từ NextEngine
     */
    public function nextEngineOrder()
    {
        return $this->hasOne(NextEngineOrder::class, 'order_id', 'id');
    }
}