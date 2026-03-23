<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcPlatform extends Model
{
    protected $fillable = [
        'code',
        'name',
        'auth_type',
    ];

    public function shops()
    {
        return $this->hasMany(Shop::class, 'ec_platform_id', 'id');
    }
}
