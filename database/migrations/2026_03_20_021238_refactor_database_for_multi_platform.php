<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tạo bảng ec_platforms
        Schema::create('ec_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Mã định danh (vd: nextengine, shopify, yahoo)');
            $table->string('name')->comment('Tên hiển thị nền tảng');
            $table->string('auth_type', 50)->default('oauth2')->comment('Loại xác thực: oauth2, api_key, basic');
            $table->string('website_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert dữ liệu mặc định để không gãy logic hiện tại
        DB::table('ec_platforms')->insert([
            ['code' => 'nextengine', 'name' => 'Next Engine', 'auth_type' => 'oauth2', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'shopify', 'name' => 'Shopify', 'auth_type' => 'api_key', 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'yahoo', 'name' => 'Yahoo Shopping', 'auth_type' => 'oauth2', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Cập nhật bảng shops
        Schema::table('shops', function (Blueprint $table) {
            $table->foreignId('ec_platform_id')->nullable()->after('id')->constrained('ec_platforms')->onDelete('restrict');
            
            // Xóa các cột hardcode của NextEngine
            $table->dropColumn([
                'nextengine_domain',
                'client_id',
                'client_secret',
                'access_token',
                'refresh_token',
                'token_expires_at'
            ]);
        });

        // Gắn mặc định các shop cũ là Next Engine (id = 1)
        DB::table('shops')->update(['ec_platform_id' => 1]);

        // 3. Tạo bảng shop_connections
        Schema::create('shop_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained('shops')->onDelete('cascade');
            $table->string('status', 50)->default('disconnected')->comment('connected, disconnected, expired, error');
            
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->dateTime('expires_at')->nullable();
            
            $table->json('credentials')->nullable()->comment('Cấu hình API linh hoạt theo từng platform');
            
            $table->timestamp('last_synced_at')->nullable()->comment('Thời điểm đồng bộ thành công gần nhất');
            $table->text('error_message')->nullable();
            
            $table->timestamps();
        });

        // 4. Xóa bảng next_engine_connections thừa
        Schema::dropIfExists('next_engine_connections');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Phục hồi bảng next_engine_connections
        Schema::create('next_engine_connections', function (Blueprint $table) {
            $table->id();
            $table->string('shop_id');
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('api_base_url')->nullable();
            $table->timestamps();
        });

        // Xóa bảng shop_connections
        Schema::dropIfExists('shop_connections');

        // Phục hồi lại bảng shops
        Schema::table('shops', function (Blueprint $table) {
            $table->dropForeign(['ec_platform_id']);
            $table->dropColumn('ec_platform_id');
            
            $table->string('nextengine_domain')->nullable();
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->dateTime('token_expires_at')->nullable();
        });

        // Xóa bảng ec_platforms
        Schema::dropIfExists('ec_platforms');
    }
};