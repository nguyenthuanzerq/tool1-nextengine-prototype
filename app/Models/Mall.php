<?php

namespace App\Models;
use App\Models\Shop;
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
}