<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperTestRunAttachment
 */
class TestRunsAttachment extends Model
{
  protected $fillable = [
    'url',
    'test_run_id',
    'test_case_id',
  ];

  protected $table = 'test_runs_attachments';

  public $timestamps = false;

  public function testRun(): BelongsTo
  {
    return $this->belongsTo(TestRun::class, 'test_run_id');
  }

  public function testCase(): BelongsTo
  {
    return $this->belongsTo(TestCase::class, 'test_case_id');
  }
}
