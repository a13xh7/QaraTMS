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
        Schema::create('menu_visibilities', function (Blueprint $table) {
            $table->id();
            $table->string('menu_key')->unique();
            $table->string('menu_name');
            $table->boolean('is_visible')->default(true);
            $table->string('parent_key')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_visibilities');
    }
};
