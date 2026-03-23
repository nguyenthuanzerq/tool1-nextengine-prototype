<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ----------------------------------------------------------------------
        // PHẦN 1: DỌN DẸP SẠCH SẼ TÀN DƯ CŨ
        // ----------------------------------------------------------------------
        Schema::disableForeignKeyConstraints();

        // Xóa các bảng Order & Product cũ
        Schema::dropIfExists('order_products');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('shop_connections');
        Schema::dropIfExists('next_engine_orders'); // Bảng cũ
        Schema::dropIfExists('next_engine_connections');
        
        // Xóa các bảng gốc
        Schema::dropIfExists('shops');
        Schema::dropIfExists('ec_platforms');

        // ----------------------------------------------------------------------
        // PHẦN 2: XÂY DỰNG KIẾN TRÚC V2.0 MỚI
        // ----------------------------------------------------------------------

        // 1. MODULE CORE
        Schema::create('ec_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('auth_type', 50)->default('oauth2');
            $table->timestamps();
        });

        // Chèn sẵn nền tảng Next Engine
        DB::table('ec_platforms')->insert([
            ['code' => 'nextengine', 'name' => 'Next Engine', 'auth_type' => 'oauth2', 'created_at' => now(), 'updated_at' => now()]
        ]);

        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ec_platform_id')->constrained('ec_platforms')->onDelete('restrict');
            $table->string('shop_code')->unique();
            $table->string('shop_name');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 2. MODULE CONNECTIONS (Horizontal Scaling)
        Schema::create('next_engine_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->unique()->constrained('shops')->onDelete('cascade');
            $table->string('client_id')->nullable();
            $table->string('client_secret')->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->string('status', 50)->default('disconnected');
            $table->timestamps();
        });

        // 3. MODULE OPERATIONAL DATA (Isolated cho Next Engine)
        Schema::create('next_engine_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            $table->string('goods_id')->comment('Mã SP gốc trên NE');
            $table->string('goods_name')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });

        Schema::create('next_engine_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
            
            // Các field chuẩn theo Next Engine (Đủ 12 trường UI cần)
            $table->string('receive_order_id')->index();
            $table->dateTime('receive_order_date')->nullable();
            $table->decimal('receive_order_goods_amount', 12, 2)->default(0);
            $table->decimal('receive_order_delivery_fee_amount', 12, 2)->default(0);
            
            $table->string('receive_order_purchaser_id')->nullable();
            $table->string('receive_order_creator_name')->nullable();
            $table->string('receive_order_purchaser_tel')->nullable();
            $table->string('receive_order_purchaser_mail_address')->nullable();
            $table->string('receive_order_purchaser_address1')->nullable();
            $table->string('receive_order_purchaser_address2')->nullable();
            
            $table->string('receive_order_delivery_method_name')->nullable();
            $table->string('receive_order_order_status_id')->nullable();
            $table->string('tracking_number')->nullable();
            
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });

        Schema::create('next_engine_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('next_engine_order_id')->constrained('next_engine_orders')->onDelete('cascade');
            $table->foreignId('next_engine_product_id')->nullable()->constrained('next_engine_products')->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('next_engine_order_items');
        Schema::dropIfExists('next_engine_orders');
        Schema::dropIfExists('next_engine_products');
        Schema::dropIfExists('next_engine_connections');
        Schema::dropIfExists('shops');
        Schema::dropIfExists('ec_platforms');
        Schema::enableForeignKeyConstraints();
    }
};