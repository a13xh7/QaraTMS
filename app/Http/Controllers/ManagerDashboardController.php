<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use App\Services\GitLabService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Repository;
use App\Models\JiraLeadTime;
use App\Models\GitlabMrLeadTime;
use DateTime;

class ManagerDashboardController extends Controller
{
    protected $gitLabService;

    public function __construct(GitLabService $gitLabService)
    {
        $this->middleware('auth');
        $this->middleware('permission:access_manager_dashboard');
        $this->gitLabService = $gitLabService;
    }

    /**
     * Display the main Manager Dashboard page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('manager.dashboard');
    }

    /**
     * Display the Smoke Detector page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function smokeDetector()
    {
        return view('manager.smoke_detector');
    }

    /**
     * Display the Post Mortems page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function postMortems()
    {
        return view('manager.post_mortems');
    }

    /**
     * Display the Deployment Fail Rate page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function deploymentFailRate()
    {
        // Get deployment data for the last 30 days
        $startDate = now()->subDays(30);
        $endDate = now();

        // Mock data for now - replace with actual implementation
        $deploymentData = [
            'overall_fail_rate' => 8.4,
            'total_deployments' => 342,
            'failed_deployments' => 29,
            'avg_recovery_time' => 42,
            'success_streak' => 16,
            'trend_data' => [
                'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                'fail_rates' => [12.5, 8.3, 6.7, 8.4],
                'deployment_counts' => [85, 92, 78, 87]
            ],
            'project_breakdown' => [
                'backend' => ['name' => 'Backend API', 'fail_rate' => 6.2, 'deployments' => 156],
                'frontend' => ['name' => 'Frontend Web', 'fail_rate' => 9.8, 'deployments' => 98],
                'mobile' => ['name' => 'Mobile App', 'fail_rate' => 12.4, 'deployments' => 45],
                'infrastructure' => ['name' => 'Infrastructure', 'fail_rate' => 4.1, 'deployments' => 43]
            ],
            'failure_types' => [
                'build_failure' => ['name' => 'Build Failure', 'count' => 12, 'percentage' => 41.4],
                'test_failure' => ['name' => 'Test Failure', 'count' => 8, 'percentage' => 27.6],
                'deployment_timeout' => ['name' => 'Deployment Timeout', 'count' => 5, 'percentage' => 17.2],
                'infrastructure_error' => ['name' => 'Infrastructure Error', 'count' => 4, 'percentage' => 13.8]
            ],
            'recent_failures' => [
                [
                    'project' => 'Backend API',
                    'environment' => 'Production',
                    'timestamp' => '2024-01-15 14:30:00',
                    'duration' => '35 min',
                    'failure_type' => 'Build Failure',
                    'status' => 'Resolved'
                ],
                [
                    'project' => 'Frontend Web',
                    'environment' => 'Staging',
                    'timestamp' => '2024-01-14 16:45:00',
                    'duration' => '28 min',
                    'failure_type' => 'Test Failure',
                    'status' => 'Resolved'
                ],
                [
                    'project' => 'Mobile App',
                    'environment' => 'Production',
                    'timestamp' => '2024-01-13 09:15:00',
                    'duration' => '52 min',
                    'failure_type' => 'Deployment Timeout',
                    'status' => 'Resolved'
                ]
            ],
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];

        return view('manager.deployment_fail_rate', [
            'deploymentData' => $deploymentData,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ]);
    }
}
