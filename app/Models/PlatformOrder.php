<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformOrder extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'platform_id', 'shop_id', 'platform_order_id', 'nextengine_order_id',
        'platform_order_status', 'sync_status', 'goods_amount', 'delivery_fee',
        'total_amount', 'buyer_id', 'buyer_name', 'buyer_email', 'buyer_phone',
        'buyer_zip', 'buyer_address', 'customer_type', 'delivery_name',
        'delivery_zip', 'delivery_address', 'delivery_method', 'payment_method',
        'tracking_number', 'ordered_at', 'shipped_at', 'synced_at', 'meta',
        'raw_data',
    ];

    protected $casts = [
        'meta'         => 'array',
        'raw_data'     => 'array',
        'goods_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'ordered_at'   => 'datetime',
        'shipped_at'   => 'datetime',
        'synced_at'    => 'datetime',
    ];

    /** Read a field from the raw API response payload. */
    public function raw(string $key, mixed $default = null): mixed
    {
        $data = is_array($this->raw_data) ? $this->raw_data : [];
        return $data[$key] ?? $default;
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PlatformOrderItem::class);
    }

    public function scopeNextEngine($query)
    {
        return $query->whereHas('platform', fn ($platform) => $platform->where('key', 'nextengine'));
    }

    public function scopePending($query)
    {
        return $query->where('sync_status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('sync_status', self::STATUS_FAILED);
    }

    public function scopeIgnored($query)
    {
        return $query->where('sync_status', self::STATUS_IGNORED);
    }
}
