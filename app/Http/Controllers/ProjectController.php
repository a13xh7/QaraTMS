<?php

namespace App\Http\Controllers;

use App\Repository;
use App\TestPlan;
use App\Suite;
use App\Project;
use App\TestRun;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /*****************************************
     *  PAGES
     *****************************************/

    public function index()
    {
        $projects = Project::all();
        return view('project.list_page')->with('projects', $projects);
    }

    public function create()
    {
        if(!auth()->user()->can('add_edit_projects')) {
            abort(403);
        }

        return view('project.create_page');
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);
        $testRuns = TestRun::where('project_id', $project->id)->orderBy('created_at', 'DESC')->get();
        $repositories = $project->repositories;

        return view('project.show_page')
            ->with('project', $project)
            ->with('testRuns', $testRuns)
            ->with('repositories', $repositories);
    }

    public function edit($id)
    {
        if(!auth()->user()->can('add_edit_projects')) {
            abort(403);
        }

        $project = Project::findOrFail($id);
        return view('project.edit_page')
            ->with('project', $project);
    }

    /*****************************************
     *  CRUD
     *****************************************/

    public function store(Request $request)
    {
        if(!auth()->user()->can('add_edit_projects')) {
            abort(403);
        }

        $request->validate([
            'title' => 'required',
        ]);

        $project = new Project();

        $project->title = $request->title;
        $project->description = $request->description;

        $project->save();

        // create default test repository
        $repository = new Repository();
        $repository->project_id = $project->id;
        $repository->title = "Default";
        $repository->prefix = "D";
        $repository->description = "Default Test Repository. Test suites and test cases are located here";
        $repository->save();


        return redirect()->route('project_show_page', $project->id);
    }

    public function update(Request $request)
    {
        if(!auth()->user()->can('add_edit_projects')) {
            abort(403);
        }

        $project = Project::findOrFail($request->id);

        $project->title = $request->title;
        $project->description = $request->description;

        $project->save();

        return redirect()->route('project_show_page', $project->id);
    }

    public function destroy(Request $request)
    {
        if(!auth()->user()->can('delete_projects')) {
            abort(403);
        }

        $project = Project::findOrFail($request->id);
        $project->delete();
        return redirect()->route('project_list_page');
    }

}
