<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('shipments', function (Blueprint $table) {
        $table->id();
        $table->string('order_id');
        $table->string('shipment_status');
        $table->date('shipment_date')->nullable();
        $table->string('carrier')->nullable();
        $table->string('tracking_number')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
