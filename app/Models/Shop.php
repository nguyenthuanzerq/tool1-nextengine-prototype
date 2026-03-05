<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    protected $fillable = [
        'shop_code',
        'shop_name',
        'nextengine_domain',
        'client_id',
        'client_secret',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'status',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    
   public function getConnectionStatusAttribute(): string
{
    if (!$this->access_token || !$this->token_expires_at) {
        return 'disconnected';
    }

    if ($this->token_expires_at->isPast()) {
        return 'expired';
    }

    return 'connected';
}
protected $casts = [
    'token_expires_at' => 'datetime',
];
}
