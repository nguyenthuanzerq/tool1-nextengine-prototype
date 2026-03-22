<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_code',
        'shop_name',
    ];


    public function nextEngineOrders()
    {
        return $this->hasMany(NextEngineOrder::class);
    }
    
    public function nextEngineConnection()
    {
        return $this->hasOne(NextEngineConnection::class, 'shop_id', 'id');
    }
}
