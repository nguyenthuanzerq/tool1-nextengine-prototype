<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NextEngineConnection extends Model
{
    protected $guarded = [];
    protected $table = 'next_engine_connections';

    public function shop()
    {
        return $this->belongsTo(Shop::class, 'shop_id', 'id');
    }
}
