<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformInventory extends Model
{
    protected $fillable = [
        'platform_id',
        'shop_id',
        'product_code',
        'product_name',
        'variant_code',
        'manage_number',
        'variant_id',
        'stock',
        'available_stock',
        'reserved_stock',
        'last_synced_at',
        'meta',
    ];

    protected $casts = [
        'stock'           => 'integer',
        'available_stock' => 'integer',
        'reserved_stock'  => 'integer',
        'last_synced_at'  => 'datetime',
        'meta'            => 'array',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
