<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Repository;
use App\Models\Suite;
use App\Models\TestCase;
use App\Models\TestPlan;
use App\Models\TestRun;
use App\Enums\TestRunCaseStatus;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $suites = Suite::whereIn('id', $testSuitesIds)->orderBy('order')->get();

        $testRun->removeDeletedCasesFromResults();

        $results = $testRun->getResults();

        return view('test_run.show_page')
            ->with('project', $project)
            ->with('testRun', $testRun)
            ->with('testPlan', $testPlan)
            ->with('repository', $repository)
            ->with('testSuitesTree', $testSuitesTree)
            ->with('suites', $suites)
            ->with('testCasesIds', $testCasesIds)
            ->with('results', $results);
    }

    public function create($project_id)
    {
        if (!auth()->user()->can('add_edit_test_runs')) {
            abort(403);
        }

        $project = Project::findOrFail($project_id);
        $testPlans = TestPlan::all();

        return view('test_run.create_page')
            ->with('project', $project)
            ->with('testPlans', $testPlans);
    }

    public function edit($project_id, $test_run_id)
    {
        if (!auth()->user()->can('add_edit_test_runs')) {
            abort(403);
        }

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
        if (!auth()->user()->can('add_edit_test_runs')) {
            abort(403);
        }

        $request->validate([
            'title' => 'required',
            'test_plan_id' => 'required',
        ]);

        $testRun = new TestRun();
        $testRun->title = $request->title;
        $testRun->test_plan_id = $request->test_plan_id;
        $testRun->project_id = $request->project_id;
        $testRun->user_id = auth()->id(); // Asignar el usuario actual
        $testRun->data = $testRun->getInitialData();
        $testRun->save();

        return redirect()->route('test_run_list_page', $request->project_id);
    }

    public function update(Request $request)
    {
        if (!auth()->user()->can('add_edit_test_runs')) {
            abort(403);
        }

        $testRun = TestRun::findOrFail($request->id);

        $testRun->title = $request->title;
        $testRun->save();

        return redirect()->route('test_run_show_page', [$testRun->project_id, $testRun->id]);
    }

    public function destroy(Request $request)
    {
        if (!auth()->user()->can('delete_test_runs')) {
            abort(403);
        }

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

    /*****************************************
     *  PDF EXPORT
     *****************************************/

    public function exportToPdf($project_id, $test_run_id)
    {
        $project = Project::findOrFail($project_id);
        $testRun = TestRun::with('user')->findOrFail($test_run_id); // Cargar la relación user
        $testPlan = TestPlan::findOrFail($testRun->test_plan_id);
        $repository = Repository::findOrFail($testPlan->repository_id);

        $testCasesIds = explode(',', $testPlan->data);
        $testCases = TestCase::whereIn('id', $testCasesIds)->get();

        $results = $testRun->getResults();
        $chartData = $testRun->getChartData();

        // Helper function to convert status number to text
        $getStatusText = function ($status) {
            switch ($status) {
                case TestRunCaseStatus::PASSED:
                    return 'PASSED';
                case TestRunCaseStatus::FAILED:
                    return 'FAILED';
                case TestRunCaseStatus::BLOCKED:
                    return 'BLOCKED';
                case TestRunCaseStatus::NOT_TESTED:
                default:
                    return 'NOT_TESTED';
            }
        };

        // Preparar datos para el PDF
        $testCasesWithResults = [];
        foreach ($testCases as $testCase) {
            $suite = Suite::find($testCase->suite_id);
            $status = $results[$testCase->id] ?? TestRunCaseStatus::NOT_TESTED;

            $testCasesWithResults[] = [
                'id' => $testCase->id,
                'title' => $testCase->title,
                'suite_name' => $suite ? $suite->title : 'N/A',
                'status' => $getStatusText($status),
                'priority' => $testCase->priority ?? 'Medium',
            ];
        }

        $data = [
            'project' => $project,
            'testRun' => $testRun,
            'testPlan' => $testPlan,
            'repository' => $repository,
            'testCases' => $testCasesWithResults,
            'chartData' => $chartData,
            'totalCases' => count($testCasesWithResults),
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'currentLocale' => app()->getLocale(),
            'executor' => $testRun->user // Información del usuario ejecutor
        ];

        $pdf = PDF::loadView('test_run.pdf_report', $data);

        $filename = 'TestRun_' . $testRun->id . '_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($filename);
    }
}
