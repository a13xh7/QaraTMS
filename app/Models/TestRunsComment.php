<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperTestRunComment
 */
class TestRunsComment extends Model
{
  protected $fillable = [
    'user_id',
    'comments',
    'test_run_id',
    'test_case_id',
  ];

  protected $table = 'test_runs_comments';

  public $timestamps = false;

  public $incrementing = false;

  protected $keyType = 'integer';

  public function testRun(): BelongsTo
  {
    return $this->belongsTo(TestRun::class, 'test_run_id');
  }

  public function testPlan(): BelongsTo
  {
    return $this->belongsTo(TestPlan::class, 'test_plan_id');
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
