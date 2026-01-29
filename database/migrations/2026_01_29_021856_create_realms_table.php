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
        Schema::create('realms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->boolean('enabled')->default(true);
            $table->integer('access_token_lifespan')->default(300);
            $table->integer('refresh_token_lifespan')->default(1800);
            $table->integer('sso_session_idle_timeout')->default(1800);
            $table->integer('sso_session_max_lifespan')->default(36000);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realms');
    }
};
