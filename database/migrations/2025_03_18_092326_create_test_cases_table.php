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
        Schema::create('test_cases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('suite_id')->index('test_cases_suite_id_foreign');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('labels')->nullable();
            $table->boolean('automated')->default(false);
            $table->integer('priority')->default(2);
            $table->longText('data')->nullable();
            $table->integer('order')->nullable();
            $table->boolean('regression')->default(true);
            $table->string('epic_link')->nullable();
            $table->string('linked_issue')->nullable();
            $table->string('jira_key', 45)->nullable();
            $table->longText('platform')->nullable();
            $table->string('release_version')->nullable();
            $table->string('severity', 45)->nullable()->default('Moderate');
            $table->string('created_by', 45)->nullable();
            $table->string('updated_by', 45)->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at')->nullable();
        });

        // Add foreign key constraint
        Schema::table('test_cases', function (Blueprint $table) {
            $table->foreign(['suite_id'])->references(['id'])->on('suites')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_cases');
    }
};
