<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GitlabMrLeadTime extends Model
{
    protected $fillable = [
        'mr_id',
        'title',
        'author',
        'mr_created_at',
        'mr_merged_at',
        'first_commit_at',
        'time_to_merge_days',
        'time_to_merge_hours',
        'labels',
        'url',
        'repository',
        'first_commit_to_merge_days',
        'first_commit_to_merge_hours',
    ];
}
