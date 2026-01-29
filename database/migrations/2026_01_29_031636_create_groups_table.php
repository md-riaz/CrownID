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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('realm_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('groups')->onDelete('cascade');
            $table->string('name');
            $table->string('path');
            $table->timestamps();
            
            $table->unique(['realm_id', 'path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
