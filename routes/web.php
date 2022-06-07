<?php

use App\Suite;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\ProjectController;
use \App\Http\Controllers\TestPlanController;
use \App\Http\Controllers\TestCaseController;
use \App\Http\Controllers\TestSuiteController;
use \App\Http\Controllers\TestRunController;
use \App\Http\Controllers\RepositoryController;
use \App\Http\Controllers\DocumentsController;



Route::post('ck-editor/imgupload', [\App\Http\Controllers\CkeditorController::class,'imgupload'])->name('ckeditor.upload');

/**********************************************************************
// test
 ***********************************************************************/
Route::get('/test', function () {
    return view('wip.test');
});

Route::get('/repo',[\App\Http\Controllers\TestController::class, 'index']);

/**********************************************************************
// AJAX
 ***********************************************************************/

Route::get('/repo/{id}',[\App\Http\Controllers\AjaxDataController::class, 'getSuitesTree']);

Route::post('/tsup',[TestSuiteController::class, 'updateParent']);
Route::post('/tsuo',[TestSuiteController::class, 'updateOrder']);



/**********************************************************************
// PROJECT
 ***********************************************************************/

Route::get('/', [ProjectController::class, 'index'])
    ->name("project_list_page");

Route::get('/project/create', [ProjectController::class, 'create'])
    ->name("project_create_page");

Route::get('/project/{id}', [ProjectController::class, 'show'])
    ->name("project_show_page");

Route::get('/project/{id}/edit', [ProjectController::class, 'edit'])
    ->name("project_edit_page");


Route::post('/project/create', [ProjectController::class, 'store'])->name("project_create");
Route::post('/project/update', [ProjectController::class, 'update'])->name("project_update");
Route::post('/project/delete', [ProjectController::class, 'destroy'])->name("project_delete");


/**********************************************************************
// REPOSITORY
 ***********************************************************************/

Route::get('/project/{project_id}/repositories', [RepositoryController::class, 'index'])
    ->where('project_id', '[0-9]+')
    ->name("repository_list_page");

Route::get('/project/{project_id}/repository/create', [RepositoryController::class, 'create'])
    ->where('project_id', '[0-9]+')
    ->name("repository_create_page");

Route::get('/project/{project_id}/repository/{repository_id}', [RepositoryController::class, 'show'])
    ->where('project_id', '[0-9]+')
    ->where('repository_id', '[0-9]+')
    ->name("repository_show_page");

Route::get('/project/{project_id}/repository/{repository_id}/edit', [RepositoryController::class, 'edit'])
    ->where('project_id', '[0-9]+')
    ->where('repository_id', '[0-9]+')
    ->name("repository_edit_page");


Route::post('/repository/create', [RepositoryController::class, 'store'])->name("repository_create");
Route::post('/repository/update', [RepositoryController::class, 'update'])->name("repository_update");
Route::post('/repository/delete', [RepositoryController::class, 'destroy'])->name("repository_delete");


Route::get('/tscl/{test_suite_id}', [TestSuiteController::class, 'loadCasesList'])
    ->where('test_suite_id', '[0-9]+');

/**********************************************************************
// TEST SUITE
 ***********************************************************************/

// Test suite editor - return form html code
// it's create an update form in one
Route::get('/tse/{operation}/{repository_id}/{test_suite_id?}', [TestSuiteController::class, 'loadEditor'])
    ->where('operation', 'create|update')
    ->where('repository_id', '[0-9]+')
    ->where('test_suite_id', '[0-9]+');

Route::post('/test-suite/create', [TestSuiteController::class, 'store'])->name("test_suite_create"); // returns test suite html for the tree
Route::post('/test-suite/update', [TestSuiteController::class, 'update'])->name("test_suite_update");
Route::post('/test-suite/delete', [TestSuiteController::class, 'destroy'])->name("test_suite_delete");


/**********************************************************************
// TEST CASE
 ***********************************************************************/

Route::get('/tc/create/{repository_id}/{parent_test_suite_id?}/', [TestCaseController::class, 'loadCreateForm'])
    ->where('repository_id', '[0-9]+')
    ->where('parent_test_suite_id', '[0-9]+');

Route::get('/tc/{test_case_id}/edit', [TestCaseController::class, 'loadEditForm'])
    ->where('test_case_id', '[0-9]+');

Route::get('/tc/{test_case_id}', [TestCaseController::class, 'loadShowForm'])
    ->where('test_case_id', '[0-9]+');

Route::get('/test-case/{test_case_id}', [TestCaseController::class, 'show'])
    ->where('test_case_id', '[0-9]+')
    ->name('test_case_show_page');


Route::post('/test-case/create', [TestCaseController::class, 'store'])->name("test_case_create");
Route::post('/test-case/update', [TestCaseController::class, 'update'])->name("test_case_update");
Route::post('/test-case/delete', [TestCaseController::class, 'destroy'])->name("test_case_delete");

/**********************************************************************
// TEST PLAN
 ***********************************************************************/

Route::get('/project/{project_id}/test-plans', [TestPlanController::class, 'index'])
    ->where('project_id', '[0-9]+')
    ->name("test_plan_list_page");

Route::get('/project/{project_id}/test-plan/create', [TestPlanController::class, 'create'])
    ->where('project_id', '[0-9]+')
    ->name("test_plan_create_page");

Route::get('/project/{project_id}/test-plan/{test_plan_id}/update', [TestPlanController::class, 'edit'])
    ->where('project_id', '[0-9]+')
    ->where('test_plan_id', '[0-9]+')
    ->name("test_plan_update_page");

Route::get('/test-plan/{test_plan_id}/start-test-run', [TestPlanController::class, 'startNewTestRun'])
    ->where('test_plan_id', '[0-9]+')
    ->name('start_new_test_run');

// Html tree
Route::get('/tpt/{repository_id}', [TestPlanController::class, 'loadRepoTree'])
    ->where('repository_id', '[0-9]+');

Route::post('/test-plans/create', [TestPlanController::class, 'store'])->name("test_plan_create");
Route::post('/test-plans/update', [TestPlanController::class, 'update'])->name("test_plan_update");
Route::post('/test-plans/delete', [TestPlanController::class, 'destroy'])->name("test_plan_delete");



/*************************************
// PROJECT TEST RUN PAGES
 *************************************/

Route::get('/project/{project_id}/test-runs', [TestRunController::class, 'index'])
    ->where('project_id', '[0-9]+')
    ->name("test_run_list_page");

Route::get('/project/{project_id}/test-run/create', [TestRunController::class, 'create'])
    ->where('project_id', '[0-9]+')
    ->name("test_run_create_page");

Route::get('/project/{project_id}/test-run/{test_run_id}', [TestRunController::class, 'show'])
    ->where('project_id', '[0-9]+')
    ->where('test_run_id', '[0-9]+')
    ->name("test_run_show_page");

Route::get('/project/{project_id}/test-run/{test_run_id}/edit', [TestRunController::class, 'edit'])
    ->where('project_id', '[0-9]+')
    ->where('test_run_id', '[0-9]+')
    ->name("test_run_edit_page");

// TEST case html block
Route::get('/trc/{test_run_id}/{test_case_id}', [TestRunController::class, 'loadTestCase'])
    ->where('test_run_id', '[0-9]+')
    ->where('test_case_id', '[0-9]+');

Route::get('/trchart/{test_run_id}', [TestRunController::class, 'loadChart'])
    ->where('test_run_id', '[0-9]+');

//Update test case status in results array

Route::post('/trcs', [TestRunController::class, 'updateCaseStatus']);

Route::post('/test-run/create', [TestRunController::class, 'store'])->name("test_run_create");
Route::post('/test-run/update', [TestRunController::class, 'update'])->name("test_run_update");
Route::post('/test-run/delete', [TestRunController::class, 'destroy'])->name("test_run_delete");




/**********************************************************************
// DOCUMENTS
 ***********************************************************************/

Route::get('/project/{project_id}/documents', [DocumentsController::class, 'index'])
    ->where('project_id', '[0-9]+')
    ->name("project_documents_list_page");

Route::get('/project/{project_id}/documents/create', [DocumentsController::class, 'create'])
    ->where('project_id', '[0-9]+')
    ->name("document_create_page");

Route::get('/project/{project_id}/documents/{document_id}', [DocumentsController::class, 'show'])
    ->where('project_id', '[0-9]+')
    ->where('document_id', '[0-9]+')
    ->name("document_show_page");

Route::get('/project/{project_id}/documents/{document_id}/edit', [DocumentsController::class, 'edit'])
    ->where('project_id', '[0-9]+')
    ->where('document_id', '[0-9]+')
    ->name("document_edit_page");


Route::post('/documents/create', [DocumentsController::class, 'store'])->name("document_create");
Route::post('/documents/update', [DocumentsController::class, 'update'])->name("document_update");
Route::post('/documents/delete', [DocumentsController::class, 'destroy'])->name("document_delete");






















