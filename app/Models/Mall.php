<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mall extends Model
{
    protected $fillable = [
        'shop_id',
        'mall_name',
        'mall_code',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class)->withDefault();
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}
