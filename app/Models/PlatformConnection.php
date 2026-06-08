<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PlatformConnection extends Model
{
    protected $fillable = [
        'platform_id',
        'shop_id',
        'seller_id',
        'client_id',
        'client_secret',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
        'token_expires_at' => 'datetime',
    ];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /* Encrypt on set, decrypt on get. Guard nulls and decryption errors. */
    public function setClientIdAttribute($value)
    {
        $this->attributes['client_id'] = $value !== null ? Crypt::encryptString($value) : null;
    }

    public function getClientIdAttribute($value)
    {
        if ($value === null) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setClientSecretAttribute($value)
    {
        $this->attributes['client_secret'] = $value !== null ? Crypt::encryptString($value) : null;
    }

    public function getClientSecretAttribute($value)
    {
        if ($value === null) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setAccessTokenAttribute($value)
    {
        $this->attributes['access_token'] = $value !== null ? Crypt::encryptString($value) : null;
    }

    public function getAccessTokenAttribute($value)
    {
        if ($value === null) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setRefreshTokenAttribute($value)
    {
        $this->attributes['refresh_token'] = $value !== null ? Crypt::encryptString($value) : null;
    }

    public function getRefreshTokenAttribute($value)
    {
        if ($value === null) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
