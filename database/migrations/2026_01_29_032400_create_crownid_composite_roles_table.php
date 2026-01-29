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
        Schema::create('crownid_composite_roles', function (Blueprint $table) {
            $table->foreignId('parent_role_id')->constrained('crownid_roles')->onDelete('cascade');
            $table->foreignId('child_role_id')->constrained('crownid_roles')->onDelete('cascade');
            $table->timestamps();
            
            $table->primary(['parent_role_id', 'child_role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crownid_composite_roles');
    }
};
