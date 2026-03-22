<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tạm thời tắt kiểm tra khóa ngoại (RẤT QUAN TRỌNG)
        Schema::disableForeignKeyConstraints();

        // 2. Tiến hành drop các bảng không còn sử dụng
        Schema::dropIfExists('listing_rules');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('malls');
        Schema::dropIfExists('channels');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('sync_histories');

        // 1. Xóa trọn bộ 5 bảng của thư viện Phân quyền (Spatie Permissions)
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');

        // 2. Xóa các bảng kết nối đa nền tảng (Chưa có yêu cầu làm)
        Schema::dropIfExists('shopify_connections');
        Schema::dropIfExists('rakuten_connections');
        Schema::dropIfExists('ec_platforms');

        // 3. Dọn dẹp luôn cột khóa ngoại ở bảng shops (nếu trước đó đã lỡ tạo)
        if (Schema::hasColumn('shops', 'ec_platform_id')) {
            Schema::table('shops', function (Blueprint $table) {
                // Tùy phiên bản MySQL, có thể cần drop foreign key trước khi drop cột
                $table->dropForeign(['ec_platform_id']); 
                $table->dropColumn('ec_platform_id');
            });
        }

        // Bật lại kiểm tra khóa ngoại
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Bỏ trống vì chúng ta chủ động xóa các tính năng không cần thiết
    }
};