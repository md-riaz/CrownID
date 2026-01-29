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
        Schema::create('crownid_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realm_id')->constrained()->onDelete('cascade');
            $table->string('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('oauth_clients')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('composite')->default(false);
            $table->timestamps();
            
            $table->unique(['realm_id', 'client_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crownid_roles');
    }
};
