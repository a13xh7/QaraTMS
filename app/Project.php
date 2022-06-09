<?php

namespace App;

use Brick\Math\Exception\DivisionByZeroException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class Project extends Model
{
    public function repositories()
    {
        return $this->hasMany(Repository::class, 'project_id', 'id');
    }

    public function testPlans()
    {
        return $this->hasMany(TestPlan::class, 'project_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'project_id', 'id');
    }


    // Count Methods

    public function repositoriesCount()
    {
        return $this->repositories->count();
    }

    public function suitesCount()
    {
        $repositoryIds = Repository::where('project_id', $this->id)->pluck('id')->toArray();
        return Suite::whereIn('repository_id', $repositoryIds)->count();
    }

    public function casesCount() {
        $repositoryIds = Repository::where('project_id', $this->id)->pluck('id')->toArray();
        $suiteIds = Suite::whereIn('repository_id', $repositoryIds)->pluck('id')->toArray();
        return TestCase::whereIn('suite_id', $suiteIds)->count();
    }

    public function automatedCasesCount() {
        $repositoryIds = Repository::where('project_id', $this->id)->pluck('id')->toArray();
        $suiteIds = Suite::whereIn('repository_id', $repositoryIds)->pluck('id')->toArray();
        return TestCase::whereIn('suite_id', $suiteIds)->where('automated', true)->count();
    }

    public function testPlansCount()
    {
        return TestPlan::where('project_id', $this->id)->count();
    }

    public function testRunsCount()
    {
        return TestRun::where('project_id', $this->id)->count();
    }

    public function getAutomationPercent() {

        try {
            $totalCases = $this->casesCount();
            $automatedCases = $this->automatedCasesCount();

            return ($automatedCases * 100) /  $totalCases;
        } catch (\ErrorException $e) {
            return 0;
        }

    }
}
