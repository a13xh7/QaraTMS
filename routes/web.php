<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BugBudgetController;
use App\Http\Controllers\DefectAnalyticsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentsController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\TestCaseController;
use App\Http\Controllers\TestPlanController;
use App\Http\Controllers\TestRunController;
use App\Http\Controllers\TestSuiteController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\JiraLeadTimeController;
use App\Http\Controllers\MRLeadTimeController;
use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**********************************************************************
 * AUTH
 **********************************************************************/

Route::get('login', [AuthController::class, 'showLoginPage'])->name('login_page');
Route::post('auth', [AuthController::class, 'authorizeUser'])->name('auth');
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

/**********************************************************************
 * AJAX
 **********************************************************************/

Route::get('/repo/{repository_id}', [RepositoryController::class, 'getSuitesTree'])->where('repository_id', '[0-9]+');

Route::post('/tsup', [TestSuiteController::class, 'updateParent']);
Route::post('/tsuo', [TestSuiteController::class, 'updateOrder']);
Route::post('/tcuo', [TestCaseController::class, 'updateOrder']);


Route::middleware(['auth'])->group(function () {

    /**********************************************************************
     * INDEX
     **********************************************************************/

    Route::get('/', function () {
        // redirect to project_list_page as the landing page
        return redirect()->route('project_list_page');
    });

    /**********************************************************************
     * DASHBOARDS
     **********************************************************************/

    Route::get('/dashboard', [AnalyticsController::class, 'index'])
        ->name('dashboard');

    Route::get('/bug-budget', [BugBudgetController::class, 'index'])
        ->name('bug_budget');

    Route::get('/defect-analytics', [DefectAnalyticsController::class, 'index'])
        ->name('defect_analytics');

    Route::get('/testing-progress', [DashboardController::class, 'testingProgress'])
        ->name('testing_progress');

    Route::get('/api-dashboard', [AnalyticsController::class, 'apiDashboard'])
        ->name('api_dashboard');

    Route::get('/apps-dashboard', [AnalyticsController::class, 'appsDashboard'])
        ->name('apps_dashboard');

    /**********************************************************************
     * USERS
     **********************************************************************/

    Route::get('/users', [UsersController::class, 'index'])
        ->name("users_list_page");

    Route::get('/users/create', [UsersController::class, 'create'])
        ->name("users_create_page");

    Route::get('/users/{user_id}/edit', [UsersController::class, 'edit'])
        ->where('user_id', '[0-9]+')
        ->name("users_edit_page");


    Route::post('/user/create', [UsersController::class, 'store'])->name("user_create");
    Route::post('/user/update', [UsersController::class, 'update'])->name("user_update");
    Route::post('/user/delete', [UsersController::class, 'destroy'])->name("user_delete");
    Route::post('/user/{user_id}/toggle-active', [UsersController::class, 'toggleActive'])->name('user_toggle_active');

    /**********************************************************************
     * PROJECT
     **********************************************************************/

    Route::get('/project', [ProjectController::class, 'index'])
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
     * REPOSITORY
     **********************************************************************/

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

    /**********************************************************************
     * TEST SUITE
     **********************************************************************/

    Route::get('/tscl/{test_suite_id}', [TestSuiteController::class, 'loadCasesList'])
        ->where('test_suite_id', '[0-9]+');

    // Test suite editor - return form html code
    // it's create and update form in one
    Route::get('/tse/{operation}/{repository_id}/{test_suite_id?}', [TestSuiteController::class, 'loadEditor'])
        ->where('operation', 'create|update')
        ->where('repository_id', '[0-9]+')
        ->where('test_suite_id', '[0-9]+');

    // returns test suite html for the tree
    Route::post('/test-suite/create', [TestSuiteController::class, 'store'])->name("test_suite_create");
    Route::post('/test-suite/update', [TestSuiteController::class, 'update'])->name("test_suite_update");
    Route::post('/test-suite/delete', [TestSuiteController::class, 'destroy'])->name("test_suite_delete");

    /**********************************************************************
     * TEST CASE
     **********************************************************************/

    Route::get('/tc/create/{repository_id}/{parent_test_suite_id?}/', [TestCaseController::class, 'loadCreateForm'])
        ->where('repository_id', '[0-9]+')
        ->where('parent_test_suite_id', '[0-9]+');

    Route::get('/tc/{test_case_id}/edit', [TestCaseController::class, 'loadEditForm'])
        ->where('test_case_id', '[0-9]+');

    Route::get('/tc/{test_case_id}', [TestCaseController::class, 'loadShowForm'])
        ->where('test_case_id', '[0-9]+');

    Route::get('/test-case-overlay/{test_case_id}', [TestCaseController::class, 'loadShowOverlay'])
        ->where('test_case_id', '[0-9]+');

    Route::get('/test-case/{test_case_id}', [TestCaseController::class, 'show'])
        ->where('test_case_id', '[0-9]+')
        ->name('test_case_show_page');


    Route::post('/test-case/create', [TestCaseController::class, 'store'])->name("test_case_create");
    Route::post('/test-case/update', [TestCaseController::class, 'update'])->name("test_case_update");
    Route::post('/test-case/delete', [TestCaseController::class, 'destroy'])->name("test_case_delete");

    Route::get('/test-case/labels', [TestCaseController::class, 'loadTestCaseLabels']);

    /**********************************************************************
     * TEST PLAN
     **********************************************************************/

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
     * PROJECT TEST RUN PAGES
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
    Route::post('/test-run/update-assignee', [TestRunController::class, 'updateAssignee']);

    Route::post('/test-run/create', [TestRunController::class, 'store'])->name("test_run_create");
    Route::post('/test-run/update', [TestRunController::class, 'update'])->name("test_run_update");
    Route::post('/test-run/delete', [TestRunController::class, 'destroy'])->name("test_run_delete");

    Route::post('/comment/{test_run_id}/{test_case_id}', [TestRunController::class, 'addComment'])->name("add_comment");

    /**********************************************************************
     * DOCUMENTS
     **********************************************************************/

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
    Route::post('/documents/store', [DocumentsController::class, 'storeDocument'])->name("documents.store");
    Route::delete('/documents/delete-document/{id}', [DocumentsController::class, 'deleteDocument'])->name("documents.delete");
    Route::get('/documents/get-document/{id}', [DocumentsController::class, 'getDocument'])->name("documents.get");

    Route::get('/documents/compliance', [DocumentsController::class, 'showCompliance'])->name('documents.compliance');
    Route::get('/documents/sop-qa', [DocumentsController::class, 'showSopQa'])->name('documents.sop_qa');
    Route::get('/documents/decision-logs', [DocumentsController::class, 'showDecisionLogs'])->name('documents.decision_logs');
    Route::post('/documents/decision-logs', [DocumentsController::class, 'storeDecisionLog'])->name('documents.decision_logs.store');
    Route::get('/documents/decision-logs/{log}/edit', [DocumentsController::class, 'editDecisionLog'])->name('documents.decision_logs.edit');
    Route::put('/documents/decision-logs/{log}', [DocumentsController::class, 'updateDecisionLog'])->name('documents.decision_logs.update');
    Route::delete('/documents/decision-logs/{log}', [DocumentsController::class, 'deleteDecisionLog'])->name('documents.decision_logs.delete');
    Route::get('/documents/decision-logs/{log}/pdf', [DocumentsController::class, 'exportDecisionLogPdf'])->name('documents.decision_logs.pdf');
    Route::post('/documents/decision-logs/export-bulk', [DocumentsController::class, 'exportBulkDecisionLogs'])->name('documents.decision_logs.export_bulk');
    Route::post('/documents/decision-logs/bulk-delete', [DocumentsController::class, 'bulkDeleteDecisionLogs'])->name('documents.decision_logs.bulk_delete');
    Route::get('/documents/test-exceptions', [DocumentsController::class, 'showTestExceptions'])->name('documents.test_exceptions');
    Route::get('/documents/audit-readiness', [DocumentsController::class, 'showAuditReadiness'])->name('documents.audit_readiness');
    Route::get('/documents/knowledge-transfers', [DocumentsController::class, 'showKnowledgeTransfers'])->name('documents.knowledge_transfers');

    Route::get('/check-children/{id}', [DocumentsController::class, 'checkChildren'])->name('document.check-children');

    Route::post('/upload', [FileUploadController::class, 'uploadFileToCloud'])->name('upload.file');

    Route::post('/documents/{id}/approve', [DocumentsController::class, 'approveDocument'])->name('documents.approve');
    Route::post('/documents/update-tree', [DocumentsController::class, 'updateTree'])->name('documents.update_tree');
    Route::put('/documents/update-document/{id}', [DocumentsController::class, 'updateDocument'])->name('documents.update');
    Route::get('/documents/get-parent-options/{id}', [DocumentsController::class, 'getParentOptions'])->name('documents.getParentOptions');

    // Manager Dashboard Routes
    Route::prefix('manager')->name('manager.')->middleware(['auth', 'check_permission:access_manager_dashboard'])->group(function () {
        Route::get('/', [App\Http\Controllers\ManagerDashboardController::class, 'index'])->name('index');
        Route::get('/smoke-detector', [App\Http\Controllers\Manager\SmokeDetectorController::class, 'index'])->name('smoke_detector');
        Route::get('/alert/{id}', [App\Http\Controllers\Manager\SmokeDetectorController::class, 'getAlertDetails'])->name('alert_details');
        Route::get('/real-time-data', [App\Http\Controllers\Manager\SmokeDetectorController::class, 'fetchRealTimeData'])->name('real_time_data');
        Route::get('/post-mortems', [App\Http\Controllers\ManagerDashboardController::class, 'postMortems'])->name('post_mortems');
        Route::get('/monthly-contribution', [App\Http\Controllers\MonthlyContributionController::class, 'index'])->name('monthly_contribution');
        Route::get('/deployment-fail-rate', [App\Http\Controllers\ManagerDashboardController::class, 'deploymentFailRate'])->name('deployment_fail_rate');
        Route::get('/lead-time-mrs', [App\Http\Controllers\MRLeadTimeController::class, 'index'])->name('lead_time_mrs');
        Route::get('/jira-lead-time', [App\Http\Controllers\JiraLeadTimeController::class, 'index'])->name('jira_lead_time');
    });

    // Settings Routes
    Route::prefix('settings')->name('settings.')->middleware(['auth', 'check_permission:access_settings'])->group(function () {
        Route::get('/', [App\Http\Controllers\SettingsController::class, 'index'])->name('index');

        // Integration Settings
        Route::get('/jira', [App\Http\Controllers\SettingsController::class, 'jira'])->name('jira');
        Route::post('/jira', [App\Http\Controllers\SettingsController::class, 'updateJira'])->name('update_jira');

        Route::get('/gitlab', [App\Http\Controllers\SettingsController::class, 'gitlab'])->name('gitlab');
        Route::post('/gitlab', [App\Http\Controllers\SettingsController::class, 'updateGitlab'])->name('update_gitlab');

        Route::get('/confluence', [App\Http\Controllers\SettingsController::class, 'confluence'])->name('confluence');
        Route::post('/confluence', [App\Http\Controllers\SettingsController::class, 'updateConfluence'])->name('update_confluence');

        // Access Control Settings
        Route::get('/dashboard-access', [App\Http\Controllers\SettingsController::class, 'dashboardAccess'])->name('dashboard_access');
        Route::post('/dashboard-access', [App\Http\Controllers\SettingsController::class, 'updateDashboardAccess'])->name('update_dashboard_access');

        Route::get('/settings-access', [App\Http\Controllers\SettingsController::class, 'settingsAccess'])->name('settings_access');
        Route::post('/settings-access', [App\Http\Controllers\SettingsController::class, 'updateSettingsAccess'])->name('update_settings_access');
        Route::post('/add-user', [App\Http\Controllers\SettingsController::class, 'addUser'])->name('add_user');

        // Menu Visibility Settings
        Route::get('/menu-visibility', [App\Http\Controllers\SettingsController::class, 'menuVisibility'])->name('menu_visibility');
        Route::post('/menu-visibility', [App\Http\Controllers\SettingsController::class, 'updateMenuVisibility'])->name('update_menu_visibility');

        // Advanced Settings
        Route::get('/advanced', [App\Http\Controllers\SettingsController::class, 'advanced'])->name('advanced');
        Route::post('/advanced', [App\Http\Controllers\SettingsController::class, 'updateAdvancedSettings'])->name('update_advanced');

        // Squad Settings
        Route::get('/squad', [App\Http\Controllers\SettingsController::class, 'squad'])->name('squad');
        Route::post('/squad', [App\Http\Controllers\SettingsController::class, 'updateSquad'])->name('update_squad');

        // Scoring Settings
        Route::get('/scoring', [App\Http\Controllers\SettingsController::class, 'scoring'])->name('scoring');
        Route::post('/scoring', [App\Http\Controllers\SettingsController::class, 'updateScoring'])->name('update_scoring');
    });
});

/**********************************************************************
 * WEB ROUTES
 * *******************************************************************/

// Test GitLab GraphQL Route (outside the auth middleware)
Route::get('/test-gitlab-graphql', function () {
    $gitLabService = app(App\Services\GitLabService::class);

    // Simple query to test GraphQL connection - query projects instead of currentUser
    $query = <<<'GRAPHQL'
    query {
      projects(first: 5) {
        nodes {
          id
          name
          fullPath
        }
      }
    }
    GRAPHQL;

    try {
        $result = $gitLabService->graphqlRequest($query);

        // Also test the projects API to compare
        $apiUrl = env('GITLAB_URL', 'https://gitlab.com');
        $token = env('GITLAB_TOKEN', '');
        $response = Http::withHeaders([
            'PRIVATE-TOKEN' => $token
        ])->get("{$apiUrl}/projects?per_page=5");

        return response()->json([
            'graphql_result' => $result,
            'rest_api_result' => $response->json(),
            'api_url' => $apiUrl,
            'graphql_url' => preg_replace('/\/api\/v4$/', '', $apiUrl) . '/api/graphql',
            'gitlab_enabled' => env('GITLAB_ENABLED'),
            'is_configured' => $gitLabService->isConfigured(),
            'token_type' => substr($token, 0, 5) . '...' // Show token type hint for debugging
        ]);
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'api_url' => env('GITLAB_URL', 'https://gitlab.com'),
            'graphql_url' => preg_replace('/\/api\/v4$/', '', env('GITLAB_URL', 'https://gitlab.com')) . '/api/graphql',
            'gitlab_enabled' => env('GITLAB_ENABLED'),
            'is_configured' => $gitLabService->isConfigured()
        ], 500);
    }
});


// API endpoint to get lead time data without rendering a view
Route::get('/api/lead-time-mrs', function (Request $request) {
    $gitLabService = app(App\Services\GitLabService::class);

    // Handle date range
    $dateRange = $request->input('date_range', 'current-month');
    $startDate = null;
    $endDate = Carbon::now();

    switch ($dateRange) {
        case 'current-month':
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            break;
        case 'last-month':
            $startDate = Carbon::now()->subMonth()->startOfMonth();
            $endDate = Carbon::now()->subMonth()->endOfMonth();
            break;
        case 'last-30-days':
            $startDate = Carbon::now()->subDays(30);
            break;
        case 'last-90-days':
            $startDate = Carbon::now()->subDays(90);
            break;
        case 'last-6-months':
            $startDate = Carbon::now()->subMonths(6);
            break;
        case 'last-year':
            $startDate = Carbon::now()->subYear();
            break;
        case 'custom':
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
            break;
        default:
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
    }

    // Format dates for GitLab API
    $startDateFormatted = $startDate->format('Y-m-d');
    $endDateFormatted = $endDate->format('Y-m-d');

    // Get filtered MRs from GitLab
    $project = $request->input('project', 'all');
    $author = $request->input('author', 'all');

    $allMergeRequests = [];
    $isConfigured = $gitLabService->isConfigured();

    if ($isConfigured) {
        // Use the new method to fetch MRs by project
        $allMergeRequests = $gitLabService->getMergedMRsFromProjects($startDateFormatted, $endDateFormatted);

        // Apply filters
        if ($project !== 'all') {
            $allMergeRequests = array_filter($allMergeRequests, function ($mr) use ($project) {
                return $mr['project'] === $project;
            });
        }

        if ($author !== 'all') {
            $allMergeRequests = array_filter($allMergeRequests, function ($mr) use ($author) {
                return $mr['author'] === $author;
            });
        }

        // Sort by merged_at date (newest first)
        usort($allMergeRequests, function ($a, $b) {
            return strtotime($b['merged_at']) - strtotime($a['merged_at']);
        });
    }

    // Calculate metrics for dashboard cards
    $totalMRs = count($allMergeRequests);
    $avgLeadTimeDays = 0;
    $avgLeadTimeHours = 0;

    if ($totalMRs > 0) {
        $avgLeadTimeDays = round(array_sum(array_column($allMergeRequests, 'lead_time_days')) / $totalMRs, 1);
        $avgLeadTimeHours = round(array_sum(array_column($allMergeRequests, 'lead_time_hours')) / $totalMRs, 1);
    }

    // Get unique projects and authors for filters
    $projects = array_unique(array_column($allMergeRequests, 'project'));
    $authors = array_unique(array_column($allMergeRequests, 'author'));

    return response()->json([
        'success' => true,
        'data' => [
            'merge_requests' => $allMergeRequests,
            'dateRange' => $dateRange,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'project' => $project,
            'author' => $author,
            'projects' => $projects,
            'authors' => $authors,
            'totalMRs' => $totalMRs,
            'avgLeadTimeDays' => $avgLeadTimeDays,
            'avgLeadTimeHours' => $avgLeadTimeHours,
            'isConfigured' => $isConfigured
        ]
    ]);
});

// Route untuk mendapatkan konten modal lampiran berdasarkan ID
Route::get('/get-attachment-modal-content/{attachmentId}', [AttachmentController::class, 'getModalContent'])->name('attachment.modal_content');

/************************************
 * Auth routes from Laravel UI/Auth
 **************************************/
// Comment out conflicting routes - using custom auth system instead
// Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
// Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
// Route::post('logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
