<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();       // 'nextengine' | 'yahoo' | 'rakuten'
            $table->string('name');                // Display name
            $table->string('auth_type');           // 'oauth2' | 'api_key' | 'basic'
            $table->json('settings')->nullable();  // base_uri, api_uri, scopes, rate_limit …
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};
