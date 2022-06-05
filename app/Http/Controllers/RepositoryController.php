<?php

namespace App\Http\Controllers;

use App\Document;
use App\Project;
use App\Repository;
use App\Suite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    /*****************************************
     *  PAGES
     *****************************************/

    public function index($project_id)
    {
        $project = Project::findOrFail($project_id);
        $repositories = $project->repositories;

        return view('repository.list_page')
            ->with('project', $project)
            ->with('repositories', $repositories);
    }

    public function create($project_id)
    {
        $project = Project::findOrFail($project_id);
        return view('repository.create_page')
            ->with('project', $project);
    }

    public function show($project_id, $repository_id)
    {
        $project = Project::findOrFail($project_id);
        $repository = Repository::findOrFail($repository_id);
        $suitesTree = Suite::where('repository_id', $repository_id)->tree()->get()->toTree();

        return view('repository.show_page')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('suitesTree', $suitesTree);
    }

    public function edit($project_id, $repository_id)
    {
        $project = Project::findOrFail($project_id);
        $repository = Repository::findOrFail($repository_id);

        return view('repository.edit_page')
            ->with('project', $project)
            ->with('repository', $repository);
    }

    /*****************************************
     *  CRUD
     *****************************************/

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'project_id' => 'required',
        ]);

        $repository = new Repository();

        $repository->title = $request->title;
        $repository->project_id = $request->project_id;
        $repository->description = $request->description;

        $repository->save();

        return redirect()->route('repository_list_page', $repository->project_id);
    }

    public function update(Request $request)
    {
        $repository = Repository::findOrFail($request->id);

        $repository->title = $request->title;
        $repository->project_id = $request->project_id;
        $repository->description = $request->description;

        $repository->save();

        return redirect()->route('repository_show_page', [$repository->project_id, $repository->id]);
    }

    public function destroy(Request $request)
    {
        $repository = Repository::findOrFail($request->id);
        $repository->delete();
        return redirect()->route('repository_list_page', $request->project_id);
    }
}
