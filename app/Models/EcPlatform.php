<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcPlatform extends Model
{
    // Chỉ định chính xác tên bảng trong Database
    protected $table = 'ec_platforms';

    // Các trường được phép Mass Assignment (insert/update an toàn)
    protected $fillable = [
        'code',
        'name',
        'auth_type',
        'website_url',
        'is_active',
    ];

    // Ép kiểu dữ liệu khi lấy từ DB ra
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Mối quan hệ 1-N: Một nền tảng EC có thể có nhiều Cửa hàng (Shop)
     */
    public function shops()
    {
        return $this->hasMany(Shop::class, 'ec_platform_id');
    }
}