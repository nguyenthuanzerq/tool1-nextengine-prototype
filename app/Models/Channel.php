<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    public function malls()
    {
        return $this->hasMany(Mall::class);
    }
}
