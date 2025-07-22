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
        // Create test_runs_attachments table
        Schema::create('test_runs_attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url');
            $table->bigInteger('test_run_id');
            $table->bigInteger('test_case_id');
            $table->timestamp('created_at');
        });

        // Create test_runs_comments table
        Schema::create('test_runs_comments', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->string('user_id', 45);
            $table->longText('comments');
            $table->bigInteger('test_run_id');
            $table->bigInteger('test_plan_id');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_runs_comments');
        Schema::dropIfExists('test_runs_attachments');
    }
};
