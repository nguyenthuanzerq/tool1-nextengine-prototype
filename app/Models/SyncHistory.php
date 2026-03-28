<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncHistory extends Model
{
    protected $table = 'sync_histories';

    protected $fillable = [
        'shop_id',
        'platform_id',
        'sync_code',
        'shop_name',
        'sync_type',
        'started_at',
        'ended_at',
        'status',
        'error_message',
        'meta',
    ];

    protected $casts = [
        'meta'       => 'array',
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class)->withDefault();
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class)->withDefault();
    }
}
