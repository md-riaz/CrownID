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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('realm_id')->after('id')->constrained()->onDelete('cascade');
            $table->string('username')->after('realm_id');
            $table->json('attributes')->nullable()->after('password');
            $table->unique(['realm_id', 'username']);
            $table->dropUnique(['email']);
            $table->unique(['realm_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['realm_id']);
            $table->dropUnique(['realm_id', 'username']);
            $table->dropUnique(['realm_id', 'email']);
            $table->dropColumn(['realm_id', 'username', 'attributes']);
            $table->unique('email');
        });
    }
};
