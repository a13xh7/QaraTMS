<?php

namespace App\Http\Controllers;

use App\Enums\TestRunCaseStatus;
use App\Repository;
use App\TestCase;
use App\TestPlan;
use App\Project;
use App\TestRun;
use App\Suite;
use Illuminate\Http\Request;

class TestRunController extends Controller
{

    public function updateCaseStatus(Request $request)
    {
        $testRun = TestRun::findOrFail($request->test_run_id);
        $results = $testRun->getResults();
        $results[$request->test_case_id] = $request->status;
        $testRun->saveResults($results);
    }
    /*****************************************
     *  PAGES
     *****************************************/

    public function index($project_id)
    {
        $project = Project::findOrFail($project_id);
        $testRuns = TestRun::where('project_id', $project->id)->orderBy('created_at', 'DESC')->get();

        return view('test_run.list_page')
            ->with('project', $project)
            ->with('testRuns', $testRuns);
    }

    public function show($project_id, $test_run_id)
    {
        $project = Project::findOrFail($project_id);
        $testRun = TestRun::findOrFail($test_run_id);
        $testPlan = TestPlan::findOrFail($testRun->test_plan_id);
        $repository = Repository::findOrFail($testPlan->repository_id);

        $testCasesIds = explode(',', $testPlan->data);
        $testSuitesIds = TestCase::whereIn('id', $testCasesIds)->get()->pluck('suite_id')->toArray();

        $testSuitesTree = Suite::whereIn('id', $testSuitesIds)->tree()->get()->toTree();

        $testRun->removeDeletedCasesFromResults();

        $results = $testRun->getResults();

        return view('test_run.show_page')
            ->with('project', $project)
            ->with('testRun', $testRun)
            ->with('testPlan', $testPlan)
            ->with('repository', $repository)
            ->with('testSuitesTree', $testSuitesTree)
            ->with('testCasesIds', $testCasesIds)
            ->with('results', $results);
    }


    public function create($project_id)
    {
        $project = Project::findOrFail($project_id);
        $testPlans = TestPlan::all();

        return view('test_run.create_page')
            ->with('project', $project)
            ->with('testPlans', $testPlans);
    }

    public function edit($project_id, $test_run_id)
    {
        $project = Project::findOrFail($project_id);
        $testRun = TestRun::findOrFail($test_run_id);

        return view('test_run.edit_page')
            ->with('project', $project)
            ->with('testRun', $testRun);
    }


    /*****************************************
     *  CRUD
     *****************************************/

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'test_plan_id' => 'required',
        ]);

        $testRun = new TestRun();
        $testRun->title = $request->title;
        $testRun->test_plan_id = $request->test_plan_id;
        $testRun->project_id = $request->project_id;
        $testRun->data = $testRun->getInitialData();
        $testRun->save();

        return redirect()->route('test_run_list_page', $request->project_id);
    }

    public function update(Request $request)
    {
        $testRun = TestRun::findOrFail($request->id);

        $testRun->title = $request->title;
        $testRun->save();

        return redirect()->route('test_run_show_page', [$testRun->project_id, $testRun->id]);
    }

    public function destroy(Request $request)
    {
        $testRun = TestRun::findOrFail($request->id);
        $testRun->delete();
        return redirect()->route('test_run_list_page', $request->project_id);
    }

    /*****************************************
     *  Test case load
     *****************************************/

    public function loadTestCase($test_run_id, $test_case_id)
    {
        $testRun = TestRun::findOrFail($test_run_id);
        $testCase = TestCase::findOrFail($test_case_id);
        $suite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($suite->repository_id);
        $data = json_decode($testCase->data);

        return view('test_run.test_case')
            ->with('repository', $repository)
            ->with('testCase', $testCase)
            ->with('testRun', $testRun)
            ->with('data', $data);
    }

    public function loadChart($test_run_id)
    {
        $testRun = TestRun::findOrFail($test_run_id);

        return view('test_run.chart')
            ->with('testRun', $testRun);
    }
}
