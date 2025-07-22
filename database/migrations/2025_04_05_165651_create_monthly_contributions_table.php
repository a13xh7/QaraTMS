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
        Schema::create('monthly_contributions', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->string('month_name');
            $table->string('username');
            $table->string('name')->nullable();
            $table->string('squad')->nullable();
            $table->integer('mr_created')->default(0);
            $table->integer('mr_approved')->default(0);
            $table->integer('repo_pushes')->default(0);
            $table->integer('total_events')->default(0);
            $table->timestamps();

            // Create unique index on year, month, and username
            $table->unique(['year', 'month', 'username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_contributions');
    }
};
