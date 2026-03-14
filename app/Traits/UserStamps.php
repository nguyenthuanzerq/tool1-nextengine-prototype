<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait UserStamps
{
    /**
     * Hàm boot tự động chạy khi Model được gọi
     */
    protected static function bootUserStamps()
    {
        // Khi tạo mới (Insert)
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id(); // Lúc mới tạo thì người tạo cũng là người cập nhật
            }
        });

        // Khi cập nhật (Update)
        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    // --- Định nghĩa Quan hệ để sau này gọi tên User dễ dàng ---
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}