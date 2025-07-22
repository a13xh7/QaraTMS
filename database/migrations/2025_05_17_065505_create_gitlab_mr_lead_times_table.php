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
        // Create GitLab MR Lead Times table
        // Create GitLab MR Lead Times table
        Schema::create('gitlab_mr_lead_times', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('repository')->nullable();
            $table->string('mr_title');
            $table->string('author');
            $table->string('mr_url');
            $table->string('mr_id');
            $table->timestamp('mr_created_at');
            $table->timestamp('merged_at')->nullable();
            $table->integer('lead_time_hours')->nullable();
            $table->string('time_to_merge_days')->nullable();
            $table->string('time_to_merge_hours')->nullable();
            $table->string('first_commit_to_merge_days')->nullable();
            $table->string('first_commit_to_merge_hours')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        // Create Jira Lead Times table
        Schema::create('jira_lead_times', function (Blueprint $table) {
            $table->id();
            $table->string('issue_key');
            $table->string('issue_type');
            $table->string('summary');
            $table->string('assignee');
            $table->string('reporter');
            $table->string('priority');
            $table->string('status');
            $table->timestamp('issue_created_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('issue_completed_date')->nullable();
            $table->integer('lead_time_hours')->nullable();
            $table->string('project_key');
            $table->string('sprint')->nullable();
            $table->string('epic_link')->nullable();
            $table->text('description')->nullable();
            $table->json('labels')->nullable();
            $table->json('components')->nullable();
            $table->string('resolution')->nullable();
            $table->timestamp('issue_updated_at');
            $table->timestamps();
        });

        // Create GitLab MR Contributors table
        Schema::create('gitlab_mr_contributors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->integer('year');
            $table->integer('month');
            $table->integer('mr_created');
            $table->integer('mr_approved');
            $table->integer('repo_push');
            $table->integer('total_events');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gitlab_mr_contributors');
        Schema::dropIfExists('jira_lead_times');
        Schema::dropIfExists('gitlab_mr_contributors');
        Schema::dropIfExists('jira_lead_times');
        Schema::dropIfExists('gitlab_mr_lead_times');
    }
};
