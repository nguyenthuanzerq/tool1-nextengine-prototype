<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NextEngineOrder extends Model
{
    protected $table = 'next_engine_orders';

    protected $fillable = [
        'order_id',
        'next_engine_order_id',
        'raw_response',
    ];
}
