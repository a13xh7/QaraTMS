<?php

namespace App\Http\Controllers;

use App\Repository;
use App\Suite;
use App\Project;


class TestController extends Controller
{
    public function index()
    {
        $project = Project::firstOrFail();
        $repository = Repository::firstOrFail();
        $suitesTree = Suite::where('repository_id', $repository->id)->orderBy('order')->tree()->get()->toTree();

        return view('wip.repo')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('suitesTree', $suitesTree);
    }



}
