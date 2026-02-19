<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'tracking_number',
    ];

     // Thêm đoạn này
    public function nextEngineOrder()
    {
        return $this->hasOne(\App\Models\NextEngineOrder::class);
    }

    // Thêm đoạn này
    public function orderProducts()
{
    return $this->hasMany(\App\Models\OrderProduct::class);
}

}
