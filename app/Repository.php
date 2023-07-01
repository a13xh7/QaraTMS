<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    public function suites()
    {
        return $this->hasMany(Suite::class, 'repository_id', 'id');
    }

    public function suitesCount()
    {
        return $this->suites->count();
    }

    public function casesCount()
    {
        $suiteIds = Suite::where('repository_id', $this->id)->pluck('id')->toArray();
        return TestCase::whereIn('suite_id', $suiteIds)->count();
    }

    public function automatedCasesCount()
    {
        $suiteIds = Suite::where('repository_id', $this->id)->pluck('id')->toArray();
        return TestCase::whereIn('suite_id', $suiteIds)->where('automated', true)->count();
    }
}
