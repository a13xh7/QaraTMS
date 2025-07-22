<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Repository;
use App\Models\Suite;
use App\Models\TestCase;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
class TestCaseController extends Controller
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

    public function store(Request $request)
    {
        if (!auth()->user()->can('add_edit_test_cases')) {
            abort(403);
        }

        $testCase = new TestCase();

        $testCase->title = $request->title;
        $testCase->description = $request->description;
        $testCase->labels = $request->labels;
        $testCase->automated = (bool) $request->automated;
        $testCase->priority = $request->priority;
        $testCase->suite_id = $request->suite_id;
        $testCase->order = $request->order;
        $testCase->data = $request->data;
        $testCase->regression = (bool) $request->regression;
        $testCase->epic_link = $request->epic_link;
        $testCase->linked_issue = $request->linked_issue;
        $testCase->platform = $request->platform;
        $testCase->release_version = $request->release_version;
        $testCase->severity = $request->severity;
        $testCase->created_by = auth()->user()->name;
        $testCase->created_at = now();
        $testCase->updated_at = now();

        $saveResult = $testCase->save();

        $suite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($suite->repository_id);

        $testCase->repository_id = $suite->repository_id;

        if ($saveResult) {
            $this->apiController->pushToLogDatabase("created", "test_case", $request);
        }

        return [
            'html' => '',
            'json' => $testCase->toJson()
        ];
    }

    public function update(Request $request)
    {
        if (!auth()->user()->can('add_edit_test_cases')) {
            abort(403);
        }

        $testCase = TestCase::findOrFail($request->id);

        $testCase->title = $request->title;
        $testCase->description = $request->description;
        $testCase->labels = $request->labels;
        $testCase->automated = (bool) $request->automated;
        $testCase->priority = $request->priority;
        $testCase->suite_id = $request->suite_id;
        $testCase->data = $request->data;
        $testCase->regression = (bool) $request->regression;
        $testCase->epic_link = $request->epic_link;
        $testCase->linked_issue = $request->linked_issue;
        $testCase->platform = $request->platform;
        $testCase->release_version = $request->release_version;
        $testCase->severity = $request->severity;
        $testCase->updated_by = auth()->user()->name;

        $saveResult = $testCase->save();

        $suite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($suite->repository_id);

        $testCase->repository_id = $suite->repository_id;

        if ($saveResult) {
            $this->apiController->pushToLogDatabase("updated", "test_case", $request);
        }

        return [
            'html' => '',
            'json' => $testCase->toJson()
        ];
    }

    public function destroy(Request $request)
    {
        if (!auth()->user()->can('delete_test_cases')) {
            abort(403);
        }

        $testCase = TestCase::findOrFail($request->id);
        $testCase->delete();
        $this->apiController->pushToLogDatabase("deleted", "test_case", request: $request);
    }

    public function updateOrder(Request $request)
    {
        foreach ($request->order as $data) {
            $testCase = TestCase::findOrFail($data['id']);
            $testCase->order = $data['order'];
            $testCase->save();
        }
    }

    /*****************************************
     *  PAGES / FORMS / HTML BLOCKS
     *****************************************/

    public function show($test_case_id)
    {
        $testCase = TestCase::findOrFail($test_case_id);
        $data = json_decode($testCase->data);
        $platform = json_decode($testCase->platform);

        $parentTestSuite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($parentTestSuite->repository_id);
        $project = Project::findOrFail($repository->project_id);

        return view('test_case.show_page')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('parentTestSuite', $parentTestSuite)
            ->with('testCase', $testCase)
            ->with('data', $data);
    }

    public function loadCreateForm($repository_id, $parent_test_suite_id = null)
    {
        if ($parent_test_suite_id != null) {
            $parentTestSuite = Suite::where('id', $parent_test_suite_id)->first();
        } else {
            $parentTestSuite = Suite::where('repository_id', $repository_id)->first();
        }

        $repository = Repository::findOrFail($parentTestSuite->repository_id);
        $project = Project::findOrFail($repository->project_id);
        $allTestCases = TestCase::with('suite.repository')->get();

        return view('test_case.create_form')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('parentTestSuite', $parentTestSuite)
            ->with('allTestCases', $allTestCases);
    }

    public function loadShowForm($test_case_id)
    {
        $testCase = TestCase::findOrFail($test_case_id);
        $data = json_decode($testCase->data);

        $platform = json_decode($testCase->platform);

        $parentTestSuite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($parentTestSuite->repository_id);
        $project = Project::findOrFail($repository->project_id);

        return view('test_case.show_form')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('parentTestSuite', $parentTestSuite)
            ->with('testCase', $testCase)
            ->with('data', $data)
            ->with('platform', $platform);
    }

    public function loadShowOverlay($test_case_id)
    {
        $testCase = TestCase::findOrFail($test_case_id);
        $data = json_decode($testCase->data);
        $platform = json_decode($testCase->platform);

        $parentTestSuite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($parentTestSuite->repository_id);
        $project = Project::findOrFail($repository->project_id);

        return view('test_case.show_overlay')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('parentTestSuite', $parentTestSuite)
            ->with('testCase', $testCase)
            ->with('data', $data)
            ->with('platform', $platform);
    }

    public function loadEditForm($test_case_id)
    {
        $testCase = TestCase::findOrFail($test_case_id);
        $allTestCases = TestCase::with('suite.repository')->get();
        $data = json_decode($testCase->data);
        $platform = json_decode($testCase->platform);

        $parentTestSuite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($parentTestSuite->repository_id);
        $project = Project::findOrFail($repository->project_id);

        return view('test_case.edit_form')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('parentTestSuite', $parentTestSuite)
            ->with('testCase', $testCase)
            ->with('allTestCases', $allTestCases)
            ->with('data', $data)
            ->with('platform', $platform);
    }

    public function loadTestCaseLabels()
    {
        $testCases = TestCase::all();
        $allLabels = [];

        // Loop through each test case and collect labels
        foreach ($testCases as $testCase) {
            $labelsArray = explode(';', $testCase->labels);
            $allLabels = array_merge($allLabels, $labelsArray);
        }

        $allLabels = array_unique($allLabels);
        return response()->json(array_values($allLabels));
    }

}
