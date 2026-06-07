<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'old_state' => 'array',
        'new_state' => 'array',
    ];
}
