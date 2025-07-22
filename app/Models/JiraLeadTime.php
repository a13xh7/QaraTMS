<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JiraLeadTime extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_key',
        'sprint',
        'jira_key',
        'summary',
        'issue_type',
        'issue_status',
        'issue_created_date',
        'issue_started_date',
        'issue_completed_date',
        'lead_time',
        'cycle_time',
        'todo_time',
        'in_progress_time',
        'code_review_time',
        'waiting_for_test_time',
        'testing_time',
        'waiting_for_acceptance_time',
        'waiting_for_deployment_time',
        'done_time',
        'assignee',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lead_time' => 'integer',
        'cycle_time' => 'integer',
        'todo_time' => 'integer',
        'in_progress_time' => 'integer',
        'code_review_time' => 'integer',
        'waiting_for_test_time' => 'integer',
        'testing_time' => 'integer',
        'waiting_for_acceptance_time' => 'integer',
        'waiting_for_deployment_time' => 'integer',
        'done_time' => 'integer',
    ];
}
