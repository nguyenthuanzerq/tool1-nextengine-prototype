<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncHistory extends Model
{
    protected $table = 'sync_histories';

    protected $fillable = [
        'sync_code',
        'shop_name',
        'sync_type',
        'started_at',
        'ended_at',
        'status',
        'error_message',
    ];
}
