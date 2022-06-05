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
        return view('project.create_page');
    }

    public function show($id)
    {
        $project = Project::findOrFail($id);
        $testRuns = TestRun::where('project_id', $project->id)->orderBy('created_at', 'DESC')->get();

        return view('project.show_page')
            ->with('project', $project)
            ->with('testRuns', $testRuns);
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        return view('project.edit_page')
            ->with('project', $project);
    }

    /*****************************************
     *  CRUD
     *****************************************/

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'prefix' => 'required',
        ]);

        $project = new Project();

        $project->title = $request->title;
        $project->prefix = strtoupper($request->prefix);
        $project->description = $request->description;

        $project->save();

        // create default test repository
        $repository = new Repository();
        $repository->project_id = $project->id;
        $repository->title = "Default";
        $repository->description = "Default Test Repository. Test suites and test cases are located here";
        $repository->save();


        return redirect()->route('project_show_page', $project->id);
    }

    public function update(Request $request)
    {
        $project = Project::findOrFail($request->id);

        $project->title = $request->title;
        $project->prefix = strtoupper($request->prefix);
        $project->description = $request->description;

        $project->save();

        return redirect()->route('project_show_page', $project->id);
    }

    public function destroy(Request $request)
    {
        $project = Project::findOrFail($request->id);
        $project->delete();
        return redirect()->route('project_list_page');
    }

}
