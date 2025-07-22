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
        Schema::create('decision_logs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('decision_type');
            $table->string('decision_owner');
            $table->string('involved_qa');
            $table->date('decision_date');
            $table->string('sprint_release')->nullable();
            $table->text('context');
            $table->text('decision');
            $table->text('impact_risk');
            $table->string('status');
            $table->json('tags')->nullable();
            $table->json('related_artifacts')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decision_logs');
    }
};
