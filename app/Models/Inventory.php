<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventories';

    protected $fillable = [
        'shop_name',
        'product_code',
        'product_name',
        'stock',
        'available_stock',
        'last_updated',
    ];

    protected $casts = [
        'stock' => 'integer',
        'available_stock' => 'integer',
        'last_updated' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scope: Filter
    |--------------------------------------------------------------------------
    | Dùng cho filter Shop - Product Code (Phase nâng cấp)
    | Không phá cấu trúc cũ
    */
    public function mall()
    {
        return $this->belongsTo(Mall::class)->withDefault();
    }

    public function scopeFilter($query, array $filters)
    {
        return $query
            ->when($filters['shop_id'] ?? null, function ($q, $shopId) {
                $q->whereHas('mall.shop', function ($qq) use ($shopId) {
                    $qq->where('id', $shopId);
                });
            })
            ->when($filters['mall_id'] ?? null, function ($q, $mallId) {
                $q->where('mall_id', $mallId);
            })
            ->when($filters['product_code'] ?? null, function ($q, $code) {
                $q->where('product_code', 'like', '%'.$code.'%');
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor: Stock Status (Optional UI enhancement)
    |--------------------------------------------------------------------------
    */

    public function getStockStatusAttribute(): string
    {
        if ($this->available_stock <= 0) {
            return 'out_of_stock';
        }

        if ($this->available_stock < 10) {
            return 'low_stock';
        }

        return 'in_stock';
    }
}
