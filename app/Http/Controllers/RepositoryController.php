<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Repository;
use App\Models\Suite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\ApiController;
class RepositoryController extends Controller
{
    /**
     * Summary of apicon
     * @var ApiController
     */
    private $apiController;

    /**
     * Summary of __construct
     * @return void
     */
    public function __construct()
    {
        $this->apiController = new ApiController();
    }
    /*****************************************
     *  AJAX
     *****************************************/

    // RETURN [ { id: 1, parent_id: 0, title: "Branch 1", level: 1 }, {}, {} ],
    public function getSuitesTree($repository_id)
    {
        $repository = Repository::findOrFail($repository_id);
        $suitesTree = Suite::where('repository_id', $repository->id)->orderBy('order')->tree()->get()->toTree();

        $jsSuitesTree = [];

        foreach ($suitesTree as $suite) {
            $this->recursiveGetData($suite, $jsSuitesTree);
        }

        return $jsSuitesTree;
    }

    private function recursiveGetData($suite, &$jsSuitesTree)
    {
        $jsSuitesTree[] = [
            'id' => $suite->id,
            'level' => $suite->depth + 1,
            'parent_id' => $suite->parent_id,
            'title' => $suite->title
        ];

        foreach ($suite->children as $suiteChild) {
            $this->recursiveGetData($suiteChild, $jsSuitesTree);
        }
    }

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
        if (!auth()->user()->can('add_edit_repositories')) {
            abort(403);
        }

        $project = Project::findOrFail($project_id);
        return view('repository.create_page')
            ->with('project', $project);
    }

    public function show($project_id, $repository_id)
    {
        $project = Project::findOrFail($project_id);
        $repository = Repository::findOrFail($repository_id);
        $suitesTree = Suite::where('repository_id', $repository_id)->orderBy('order')->tree()->get()->toTree();

        $user = Auth::user();
        $canEditSuites = $user->can('add_edit_test_suites') == true ? 1 : 0;
        $canDeleteSuites = $user->can('delete_test_suites') == true ? 1 : 0;

        return view('repository.show_page')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('suitesTree', $suitesTree)
            ->with('canEditSuites', $canEditSuites)
            ->with('canDeleteSuites', $canDeleteSuites);
    }

    public function edit($project_id, $repository_id)
    {
        if (!auth()->user()->can('add_edit_repositories')) {
            abort(403);
        }

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
        if (!auth()->user()->can('add_edit_repositories')) {
            abort(403);
        }

        $request->validate([
            'title' => 'required',
            'project_id' => 'required',
        ]);

        $repository = new Repository();

        $repository->title = $request->title;
        $repository->prefix = $request->prefix;
        $repository->project_id = $request->project_id;
        $repository->description = $request->description;

        $saveResult = $repository->save();

        if ($saveResult) {
            $this->apiController->pushToLogDatabase("created", "repository", $request);
        }

        return redirect()->route('repository_list_page', $repository->project_id);
    }

    public function update(Request $request)
    {
        if (!auth()->user()->can('add_edit_repositories')) {
            abort(403);
        }

        $repository = Repository::findOrFail($request->id);

        $repository->title = $request->title;
        $repository->prefix = $request->prefix;
        $repository->project_id = $request->project_id;
        $repository->description = $request->description;

        $saveResult = $repository->save();

        if ($saveResult) {
            $this->apiController->pushToLogDatabase("updated", "repository", $request);
        }

        return redirect()->route('repository_list_page', $repository->project_id);
    }

    public function destroy(Request $request)
    {
        if (!auth()->user()->can('delete_repositories')) {
            abort(403);
        }

        $repository = Repository::findOrFail($request->id);
        $repository->delete();
        $this->apiController->pushToLogDatabase("deleted", "repository", $request);
        return redirect()->route('repository_list_page', $request->project_id);
    }
}
