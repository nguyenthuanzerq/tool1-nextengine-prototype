<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    /**
     * 出品制御（Listing Control）
     * Skeleton version – Architecture-first
     */
     public function up(): void
    {
        Schema::create('listing_rules', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Core Reference
            |--------------------------------------------------------------------------
            */

            // ⚠ TBD-1:
            // JP: 商品はモール単位ですか？それとも共通商品ですか？
            // VN: Sản phẩm quản lý theo từng Mall hay là sản phẩm chung toàn hệ thống?
            //
            // → Tạm thời tham chiếu inventory_id (Phase 9 đang dùng)
            $table->unsignedBigInteger('inventory_id')->nullable();

            $table->unsignedBigInteger('channel_id');

            /*
            |--------------------------------------------------------------------------
            | Publish Flag
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_publishable')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Notes (Future extension)
            |--------------------------------------------------------------------------
            */

            $table->text('note')->nullable();

            /*
            |--------------------------------------------------------------------------
            | System
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Unique Constraint
            |--------------------------------------------------------------------------
            */

            $table->unique(['inventory_id', 'channel_id']);

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign('channel_id')
                ->references('id')
                ->on('channels')
                ->cascadeOnDelete();

            // inventory foreign key chưa khóa cứng vì TBD-1
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_rules');
    }
};
