<?php

namespace App\Services;

use App\Models\TestCase;
use App\Models\TestRun;
use App\Models\TestPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TestAnalyticsService
{
    /**
     * Get test execution analytics for a given period
     */
    public function getTestExecutionAnalytics(string $startDate, string $endDate, ?string $projectId = null): array
    {
        $query = TestRun::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $testRuns = $query->with(['testCases', 'project'])->get();

        return [
            'total_test_runs' => $testRuns->count(),
            'total_test_cases' => $testRuns->sum(function ($run) {
                return $run->testCases->count();
            }),
            'passed_tests' => $testRuns->sum(function ($run) {
                return $run->testCases->where('status', 'passed')->count();
            }),
            'failed_tests' => $testRuns->sum(function ($run) {
                return $run->testCases->where('status', 'failed')->count();
            }),
            'blocked_tests' => $testRuns->sum(function ($run) {
                return $run->testCases->where('status', 'blocked')->count();
            }),
            'skipped_tests' => $testRuns->sum(function ($run) {
                return $run->testCases->where('status', 'skipped')->count();
            }),
            'success_rate' => $this->calculateSuccessRate($testRuns),
            'average_execution_time' => $this->calculateAverageExecutionTime($testRuns),
            'trend_data' => $this->getTrendData($startDate, $endDate, $projectId)
        ];
    }

    /**
     * Get test coverage analytics
     */
    public function getTestCoverageAnalytics(?string $projectId = null): array
    {
        $query = TestCase::query();

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $totalTestCases = $query->count();
        $executedTestCases = $query->whereHas('testRuns')->count();

        return [
            'total_test_cases' => $totalTestCases,
            'executed_test_cases' => $executedTestCases,
            'coverage_percentage' => $totalTestCases > 0 ? ($executedTestCases / $totalTestCases) * 100 : 0,
            'unexecuted_test_cases' => $totalTestCases - $executedTestCases
        ];
    }

    /**
     * Get test plan analytics
     */
    public function getTestPlanAnalytics(string $startDate, string $endDate, ?string $projectId = null): array
    {
        $query = TestPlan::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $testPlans = $query->with(['testCases', 'testRuns'])->get();

        return [
            'total_test_plans' => $testPlans->count(),
            'active_test_plans' => $testPlans->where('is_active', true)->count(),
            'completed_test_plans' => $testPlans->where('status', 'completed')->count(),
            'in_progress_test_plans' => $testPlans->where('status', 'in_progress')->count(),
            'average_test_cases_per_plan' => $testPlans->count() > 0 ? 
                $testPlans->sum(function ($plan) {
                    return $plan->testCases->count();
                }) / $testPlans->count() : 0
        ];
    }

    /**
     * Calculate success rate from test runs
     */
    private function calculateSuccessRate(Collection $testRuns): float
    {
        $totalTests = $testRuns->sum(function ($run) {
            return $run->testCases->count();
        });

        if ($totalTests === 0) {
            return 0;
        }

        $passedTests = $testRuns->sum(function ($run) {
            return $run->testCases->where('status', 'passed')->count();
        });

        return ($passedTests / $totalTests) * 100;
    }

    /**
     * Calculate average execution time
     */
    private function calculateAverageExecutionTime(Collection $testRuns): float
    {
        $totalTime = $testRuns->sum(function ($run) {
            return $run->execution_time ?? 0;
        });

        return $testRuns->count() > 0 ? $totalTime / $testRuns->count() : 0;
    }

    /**
     * Get trend data for charts
     */
    private function getTrendData(string $startDate, string $endDate, ?string $projectId = null): array
    {
        $query = TestRun::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total_runs'),
                DB::raw('SUM(CASE WHEN status = "passed" THEN 1 ELSE 0 END) as passed'),
                DB::raw('SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $trendData = $query->get();

        return [
            'labels' => $trendData->pluck('date')->toArray(),
            'datasets' => [
                'total_runs' => $trendData->pluck('total_runs')->toArray(),
                'passed' => $trendData->pluck('passed')->toArray(),
                'failed' => $trendData->pluck('failed')->toArray()
            ]
        ];
    }
} 