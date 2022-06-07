<?php

namespace App\Http\Controllers;

use App\Project;
use App\Repository;
use App\TestCase;
use App\Suite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use mysql_xdevapi\Exception;

class TestCaseController extends Controller
{
    public function store(Request $request)
    {
        $testCase = new TestCase();

        $testCase->title = $request->title;
        $testCase->automated = (bool)$request->automated;
        $testCase->priority = $request->priority;
        $testCase->suite_id = $request->suite_id;
        $testCase->data = $request->data;

        $testCase->save();

        $suite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($suite->repository_id);
        $project = Project::findOrFail($repository->project_id);

        $testCase->repository_id = $suite->repository_id;  // это нужно для загрузки формы  read в js

        return [
            'html' => view('repository.new_test_case')
                    ->with('testCase', $testCase)
                    ->with('suite', $suite)
                    ->with('project', $project)->render(),
            'json' => $testCase->toJson()
        ];
    }

    public function update(Request $request) {
        $testCase = TestCase::findOrFail($request->id);

        $testCase->title = $request->title;
        $testCase->automated = (bool)$request->automated;
        $testCase->priority = $request->priority;
        $testCase->suite_id = $request->suite_id;
        $testCase->data = $request->data;

        $testCase->save();

        $suite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($suite->repository_id);
        $project = Project::findOrFail($repository->project_id);

        $testCase->repository_id = $suite->repository_id;  // это нужно для загрузки формы в js

        return [
            'html' => view('repository.new_test_case')
                ->with('testCase', $testCase)
                ->with('suite', $suite)
                ->with('project', $project)->render(),
            'json' => $testCase->toJson()
        ];
    }

    public function destroy(Request $request) {
        $testCase = TestCase::findOrFail($request->id);
        $testCase->delete();
    }

    /*****************************************
     *  PAGES / FORMS / HTML BLOCKS
     *****************************************/

    public function show($test_case_id) {
        $testCase = TestCase::findOrFail($test_case_id);
        $data = json_decode($testCase->data);

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

    public function loadCreateForm($repository_id, $parent_test_suite_id=null)
    {
        if($parent_test_suite_id != null) {
            $parentTestSuite = Suite::where('id', $parent_test_suite_id)->first();
        } else {
            $parentTestSuite = Suite::where('repository_id', $repository_id)->first();
        }

        $repository = Repository::findOrFail($parentTestSuite->repository_id);
        $project = Project::findOrFail($repository->project_id);

        return view('test_case.create_form')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('parentTestSuite', $parentTestSuite);
    }

    public function loadShowForm($test_case_id)
    {
        $testCase = TestCase::findOrFail($test_case_id);
        $data = json_decode($testCase->data);

        $parentTestSuite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($parentTestSuite->repository_id);
        $project = Project::findOrFail($repository->project_id);

        return view('test_case.show_form')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('parentTestSuite', $parentTestSuite)
            ->with('testCase', $testCase)
            ->with('data', $data);
    }

    public function loadEditForm($test_case_id)
    {
        $testCase = TestCase::findOrFail($test_case_id);
        $data = json_decode($testCase->data);

        $parentTestSuite = Suite::findOrFail($testCase->suite_id);
        $repository = Repository::findOrFail($parentTestSuite->repository_id);
        $project = Project::findOrFail($repository->project_id);

        return view('test_case.edit_form')
            ->with('project', $project)
            ->with('repository', $repository)
            ->with('parentTestSuite', $parentTestSuite)
            ->with('testCase', $testCase)
            ->with('data', $data);
    }

}
