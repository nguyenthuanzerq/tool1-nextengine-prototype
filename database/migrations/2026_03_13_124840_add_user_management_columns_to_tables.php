<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Thêm trạng thái khóa/mở vào bảng users
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('password')->comment('1: Hoạt động, 0: Bị khóa');
        });

        // 2. Thêm tracking vào bảng shops
        Schema::table('shops', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('status');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
        });

        // 3. Thêm tracking vào bảng orders
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('status');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};