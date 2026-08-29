<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform_id',
        'shop_code',
        'shop_name',
        'client_id',
        'client_secret',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'auto_sync_enabled',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at'  => 'datetime',
            'auto_sync_enabled' => 'boolean',
        ];
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function platformConnections()
    {
        return $this->hasMany(PlatformConnection::class);
    }

    public function platformConnection(Platform $platform)
    {
        return $this->platformConnections()->where('platform_id', $platform->id)->first();
    }

    public function syncHistories()
    {
        return $this->hasMany(SyncHistory::class);
    }

    public function latestSyncHistory()
    {
        return $this->hasOne(SyncHistory::class)->latestOfMany('started_at');
    }
}
