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
    Schema::create('shops', function (Blueprint $table) {
        $table->id();

        $table->string('shop_code')->unique(); // ShopID hiển thị trên UI
        $table->string('shop_name');

        $table->string('nextengine_domain')->nullable();
        $table->string('client_id')->nullable();
        $table->string('client_secret')->nullable();

        $table->text('access_token')->nullable();
        $table->text('refresh_token')->nullable();
        $table->dateTime('token_expires_at')->nullable();

        $table->unsignedTinyInteger('status')->default(1);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
