<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('malls', function (Blueprint $table) {
            $table->foreignId('channel_id')
                ->nullable()
                ->after('id') // nếu malls có cột khác quan trọng, anh đổi after(...) cho hợp lý
                ->constrained('channels')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index('channel_id');
        });
    }

    public function down(): void
    {
        Schema::table('malls', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
            $table->dropIndex(['channel_id']);
            $table->dropColumn('channel_id');
        });
    }
};
