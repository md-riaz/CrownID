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
        Schema::table('realms', function (Blueprint $table) {
            $table->boolean('mfa_enabled')->default(false)->after('name');
            $table->boolean('brute_force_protected')->default(true)->after('mfa_enabled');
            $table->integer('max_login_attempts')->default(5)->after('brute_force_protected');
            $table->integer('lockout_duration_minutes')->default(30)->after('max_login_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realms', function (Blueprint $table) {
            $table->dropColumn([
                'mfa_enabled',
                'brute_force_protected',
                'max_login_attempts',
                'lockout_duration_minutes'
            ]);
        });
    }
};
