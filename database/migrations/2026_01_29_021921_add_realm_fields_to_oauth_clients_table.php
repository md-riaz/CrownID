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
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->foreignId('realm_id')->after('id')->constrained()->onDelete('cascade');
            $table->string('client_id')->after('realm_id')->unique();
            $table->string('client_type')->default('confidential')->after('secret');
            $table->boolean('enabled')->default(true)->after('revoked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_clients', function (Blueprint $table) {
            $table->dropForeign(['realm_id']);
            $table->dropColumn(['realm_id', 'client_id', 'client_type', 'enabled']);
        });
    }
};
