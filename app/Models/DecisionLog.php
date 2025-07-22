<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DecisionLog extends Model
{
    use HasFactory;

    protected $table = 'decision_logs';

    protected $fillable = [
        'title',
        'decision_type',
        'decision_owner',
        'involved_qa',
        'decision_date',
        'sprint_release',
        'context',
        'decision',
        'impact_risk',
        'status',
        'tags',
        'related_artifacts',
    ];

    protected $casts = [
        'tags' => 'array',
        'related_artifacts' => 'array',
        'decision_date' => 'date',
    ];
}
