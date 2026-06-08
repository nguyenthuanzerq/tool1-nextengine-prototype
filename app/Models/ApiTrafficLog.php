<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiTrafficLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'request_headers' => 'array',
        'request_payload' => 'array',
    ];
}
