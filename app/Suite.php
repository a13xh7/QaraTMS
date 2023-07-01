<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class Suite extends Model
{
    use HasRecursiveRelationships;

    public function testCases()
    {
        return $this->hasMany(TestCase::class, 'suite_id', 'id');
    }

    public function testCasesCount()
    {
        return $this->testCases->count();
    }

    public function delete() {
      $descendants = $this->descendants;

      foreach ($descendants as $descendant) {
        $descendant->delete();
      }
      return parent::delete();
    }

}
