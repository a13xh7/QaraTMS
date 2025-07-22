<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Repository;
use App\Models\Suite;
use App\Models\TestCase;
use App\Models\TestPlan;
use App\Models\TestRun;
use App\Models\TestRunsAttachment;
use App\Models\TestRunsComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ApiController;
class TestRunController extends Controller
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
        $testPlans = TestPlan::where('project_id', $project->id)->orderBy('created_at', 'DESC')->get();

        return view('test_run.list_page')
            ->with('project', $project)
            ->with('testRuns', $testRuns)
            ->with('testPlans', $testPlans);
    }

    public function show($project_id, $test_run_id)
    {
        $project = Project::findOrFail($project_id);
        $testRun = TestRun::findOrFail($test_run_id);
        $testPlan = TestPlan::findOrFail($testRun->test_plan_id);
        $repository = Repository::all();

        $testCasesIds = explode(',', $testPlan->data);
        $testSuitesIds = TestCase::whereIn('id', $testCasesIds)->get()->pluck('suite_id')->toArray();

        $testSuitesTree = Suite::whereIn('id', $testSuitesIds)->tree()->get()->toTree();
        $suites = Suite::whereIn('id', $testSuitesIds)->orderBy('order')->get();

        $testRun->removeDeletedCasesFromResults();
        $users = User::all();

        $results = $testRun->getResults();
        $assignee = $testRun->getAssignee();

        return view('test_run.show_page')
            ->with('project', $project)
            ->with('testRun', $testRun)
            ->with('testPlan', $testPlan)
            ->with('repository', $repository)
            ->with('testSuitesTree', $testSuitesTree)
            ->with('suites', $suites)
            ->with('testCasesIds', $testCasesIds)
            ->with('results', $results)
            ->with('testAssignee', $assignee)
            ->with('users', $users);
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
        $testRun->data = $testRun->getInitialData();
        $testResult = $testRun->save();

        if ($testResult) {
            $this->apiController->pushToLogDatabase("created and run", "test_run", $request);
        }

        return redirect()->route('test_run_list_page', $request->project_id);
    }

    public function update(Request $request)
    {
        if (!auth()->user()->can('add_edit_test_runs')) {
            abort(403);
        }

        $testRun = TestRun::findOrFail($request->id);

        $testRun->title = $request->title;
        $testResult = $testRun->save();

        if ($testResult) {
            $this->apiController->pushToLogDatabase("updated", "test_run", $request);
        }

        return redirect()->route('test_run_show_page', [$testRun->project_id, $testRun->id]);
    }

    public function destroy(Request $request)
    {
        if (!auth()->user()->can('delete_test_runs')) {
            abort(403);
        }
        $testRun = TestRun::findOrFail($request->id);
        $testResult = $testRun->delete();
        if ($testResult) {
            $this->apiController->pushToLogDatabase("deleted", "test_run", $request);
        }
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
        $results = $testRun->getResults();
        $attachments = TestRunsAttachment::where('test_run_id', $test_run_id)->where('test_case_id', $test_case_id)->get();
        $comments = TestRunsComment::where('test_run_id', $test_run_id)->where('test_case_id', $test_case_id)->get();
        $users = User::all();

        return view('test_run.test_case')
            ->with('repository', $repository)
            ->with('testCase', $testCase)
            ->with('testRun', $testRun)
            ->with('data', $data)
            ->with('results', $results)
            ->with('attachments', $attachments)
            ->with('comments', $comments)
            ->with('users', $users);
    }

    public function loadChart($test_run_id)
    {
        $testRun = TestRun::findOrFail($test_run_id);
        return view('test_run.chart')
            ->with('testRun', $testRun);
    }

    public function addComment($test_run_id, $test_case_id, Request $request)
    {

        $testRun = TestRun::findOrFail($test_run_id);
        $testCase = TestCase::findOrFail($test_case_id);
        $suite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($suite->repository_id);
        $data = json_decode($testCase->data);
        $results = $testRun->getResults();
        $users = User::all();

        $addComment = new TestRunsComment();
        $addComment->user_id = auth()->user()->id;
        $addComment->test_run_id = $test_run_id;
        $addComment->test_case_id = $test_case_id;
        $addComment->comments = $request->comment;
        $addComment->created_at = now();
        $addComment->save();

        // Get the last comment
        $lastComment = TestRunsComment::where('test_run_id', $test_run_id)
            ->where('test_case_id', $test_case_id)
            ->where('user_id', auth()->user()->id)
            ->latest('created_at')
            ->first();

        if ($request->hasFile('files')) {
            $fileUploadController = new FileUploadController();
            $response = $fileUploadController->uploadFileToCloud($request);
            $fileUrl = $response->original['data'];

            foreach ($fileUrl as $url) {
                $addAttachment = new TestRunsAttachment();
                $addAttachment->test_run_id = $test_run_id;
                $addAttachment->test_case_id = $test_case_id;
                $addAttachment->comment_id = $lastComment->id;
                $addAttachment->public_url = $url;
                $addAttachment->created_at = now();
                $addAttachment->updated_at = now();
                $addAttachment->user_id = auth()->user()->id;
                $addAttachment->save();
            }
        }

        if ($request->ajax()) {
            $attachments = TestRunsAttachment::where('test_run_id', $test_run_id)->where('test_case_id', $test_case_id)->get();
            $comments = TestRunsComment::where('test_run_id', $test_run_id)->where('test_case_id', $test_case_id)->get();

            return view('test_run.test_case')
                ->with('repository', $repository)
                ->with('testCase', $testCase)
                ->with('testRun', $testRun)
                ->with('data', $data)
                ->with('results', $results)
                ->with('attachments', $attachments)
                ->with('comments', $comments)
                ->with('users', $users);
        }

        return redirect()->back();
    }

    public function updateAssignee(Request $request)
    {
        $testRun = TestRun::findOrFail($request->test_run_id);
        $currentAssignee = $testRun->getAssignee();
        $currentAssignee[$request->test_case_id] = $request->user_id;
        $testRun->assignee = $currentAssignee;
        $testRun->save();

        return response()->json(['success' => true]);
    }
}
