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
        // Create allure_report table
        Schema::create('allure_report', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('squad', 45)->nullable()->default('SWAT');
            $table->string('app_version', 45);
            $table->string('status', 45);
            $table->integer('total_scenario');
            $table->integer('total_passed');
            $table->integer('total_failed');
            $table->integer('total_skipped');
            $table->integer('execution_duration');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });

        // Create allure_scenarios table
        Schema::create('allure_scenarios', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('allure_report_id')->index('allure_report_id_idx');
            $table->longText('scenario_name');
            $table->string('final_status', 45);
            $table->integer('duration');
            $table->longText('error_message');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });

        // Create allure_steps table
        Schema::create('allure_steps', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('scenario_id')->index('scenario_id_idx');
            $table->string('step_name', 225);
            $table->string('step_status', 45);
            $table->string('attachment_urls', 225)->nullable();
            $table->integer('step_duration');
            $table->integer('step_index');
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
        });

        // Add foreign key constraints
        Schema::table('allure_scenarios', function (Blueprint $table) {
            $table->foreign(['allure_report_id'], 'allure_report_id')->references(['id'])->on('allure_report')->onUpdate('no action')->onDelete('no action');
        });

        Schema::table('allure_steps', function (Blueprint $table) {
            $table->foreign(['scenario_id'], 'scenario_id')->references(['id'])->on('allure_scenarios')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allure_steps');
        Schema::dropIfExists('allure_scenarios');
        Schema::dropIfExists('allure_report');
    }
};
