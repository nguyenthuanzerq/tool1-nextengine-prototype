<?php

namespace App\Models;

// INVENTORY LEGACY MODEL: candidate for deletion with inventory feature removal
use Illuminate\Database\Eloquent\Model;

class PlatformInventory extends Model
{
    protected $fillable = [
        'platform_id',
        'shop_id',
        'product_code',
        'product_name',
        'variant_code',
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
