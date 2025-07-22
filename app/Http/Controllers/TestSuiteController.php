<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use App\Models\Suite;
use App\Models\TestCase;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
class TestSuiteController extends Controller
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
    /******************************************
     *  AJAX and html elements
     *****************************************/

    public function updateParent(Request $request)
    {
        if (!auth()->user()->can('add_edit_test_suites')) {
            abort(403);
        }

        $testSuite = Suite::findOrFail($request->id);
        $testSuite->parent_id = $request->parent_id;
        $testSuite->save();
    }

    public function updateOrder(Request $request)
    {
        if (!auth()->user()->can('add_edit_test_suites')) {
            abort(403);
        }

        foreach ($request->order as $data) {
            $testSuite = Suite::findOrFail($data['id']);
            $testSuite->order = $data['order'];
            $testSuite->save();
        }
    }

    public function loadEditor($operation, $repository_id, $test_suite_id = null)
    {
        $repository = Repository::findOrFail($repository_id);
        $editableSuite = isset($test_suite_id) ? Suite::findOrFail($test_suite_id) : null;

        $suitesTree = Suite::where('repository_id', $repository_id)->tree()->get()->toTree();

        return view('test_suite.editor')
            ->with('operation', $operation)
            ->with('repository', $repository)
            ->with('editableSuite', $editableSuite)
            ->with('suitesTree', $suitesTree);
    }

    public function loadCasesList($test_suite_id)
    {
        $suite = Suite::findOrFail($test_suite_id);
        $repository = Repository::findOrFail($suite->repository_id);
        $testCases = TestCase::select('id', 'suite_id', 'title', 'automated', 'priority', 'order')->where(
            'suite_id',
            $test_suite_id
        )->orderBy('order')->get();

        return view('repository.test_cases_list')
            ->with('testCases', $testCases)
            ->with('repository', $repository);
    }

    /******************************************
     *  CRUD
     *****************************************/

    public function store(Request $request)
    {
        if (!auth()->user()->can('add_edit_test_suites')) {
            abort(403);
        }

        $suite = new Suite();

        $suite->repository_id = $request->repository_id;
        $suite->parent_id = $request->parent_id;
        $suite->title = $request->title;
        $result = $suite->save();
        if ($result) {
            $this->apiController->pushToLogDatabase("created", "test_suite", $request);
        }

        return [
            'html' => '',
            'json' => $suite->toJson()
        ];
    }

    public function update(Request $request)
    {
        if (!auth()->user()->can('add_edit_test_suites')) {
            abort(403);
        }

        $testSuite = Suite::findOrFail($request->id);

        $testSuite->title = $request->title;
        $result = $testSuite->save();
        if ($result) {
            $this->apiController->pushToLogDatabase("updated", "test_suite", $request);
        }
    }

    public function destroy(Request $request)
    {
        if (!auth()->user()->can('delete_test_suites')) {
            abort(403);
        }

        $testSuite = Suite::findOrFail($request->id);
        $result = $testSuite->delete();
        if ($result) {
            $this->apiController->pushToLogDatabase("deleted", "test_suite", $request);
        }
    }
}
