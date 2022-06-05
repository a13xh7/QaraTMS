<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Suite extends Model
{
    use \Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

    public function testCases()
    {
        return $this->hasMany(TestCase::class, 'suite_id', 'id');
    }

    public function testCasesCount()
    {
        return $this->testCases->count();
    }

}
