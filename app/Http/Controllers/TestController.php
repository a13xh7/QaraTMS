<?php

namespace App\Http\Controllers;

use App\Repository;
use App\TestPlan;
use App\Suite;
use App\Project;
use App\TestRun;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index()
    {
        $project = Project::firstOrFail();
        $repository = Repository::firstOrFail();
        $suitesTree = Suite::where('repository_id', $repository->id)->tree()->get()->toTree();

        return view('repo_wip')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('suitesTree', $suitesTree);
    }



}
