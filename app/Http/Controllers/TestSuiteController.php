<?php

namespace App\Http\Controllers;

use App\Repository;
use App\Suite;
use App\Project;
use App\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class TestSuiteController extends Controller
{
    /******************************************
     *  AJAX and html elements
     *****************************************/

    public function updateParent(Request $request)
    {
        $testSuite = Suite::findOrFail($request->id);
        $testSuite->parent_id = $request->parent_id;
        $testSuite->save();
    }

    public function updateOrder(Request $request)
    {
        foreach($request->order as $data){
            $testSuite = Suite::findOrFail($data['id']);
            $testSuite->order = $data['order'];
            $testSuite->save();
        }
    }

    public function loadEditor($operation, $repository_id, $test_suite_id=null)
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
        $project = Project::findOrFail($repository->project_id);
        $testCases = TestCase::where('suite_id', $test_suite_id)->orderBy('order')->get();

        return view('wip.test_cases_list')
            ->with('testCases', $testCases)
            ->with('project', $project);
    }

    /******************************************
     *  CRUD
     *****************************************/

    public function store(Request $request)
    {
        $suite = new Suite();

        $suite->repository_id = $request->repository_id;
        $suite->parent_id = $request->parent_id;
        $suite->title = $request->title;
        $suite->save();

        return [
            'html' => view('repository.new_suite')
                    ->with('suite', $suite)->render(),
            'json' => $suite->toJson()
        ];
    }

    public function update(Request $request)
    {
        $testSuite = Suite::findOrFail($request->id);

        $testSuite->title = $request->title;
        $testSuite->parent_id = $request->parent_id;
        $testSuite->save();

        return redirect()->route('repository_show_page', [$request->project_id, $testSuite->repository_id]);

//        return [
//            'html' => view('tree.repository_test_suite')
//                ->with('testSuite', $testSuite)
//                ->with('project', $project)->render(),
//            'json' => $testSuite->toJson()
//        ];
    }

    public function destroy(Request $request)
    {
        $testSuite = Suite::findOrFail($request->id);
        $testSuite->descendantsAndSelf()->delete();
    }


}
