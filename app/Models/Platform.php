<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $fillable = ['key', 'name', 'auth_type', 'settings'];

    protected $casts = ['settings' => 'array'];

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }

    public function connections()
    {
        return $this->hasMany(PlatformConnection::class);
    }
}
