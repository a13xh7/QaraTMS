@extends('layout.base_analytics')

@php
    $title = 'Apps Automation Dashboard';
    $subtitle = 'Monitor and analyze apps automated tests';
    $filterAction = route('apps_dashboard');
    
    // Set up filter options for the reusable component
    $filterOptions = [
        'id' => 'squad',
        'name' => 'squad',
        'label' => 'Squad',
        'items' => $squads,
        'selected' => $selectedSquad
    ];

    // Set up environment options
    $environmentOptions = [
        'items' => $environments,
        'selected' => $selectedEnvironment
    ];
@endphp

@section('dashboard_cards')
<div class="col-md-3">
    <div class="card bg-primary text-white shadow-sm h-100">
        <div class="card-body py-2 px-3">
            <div class="medium mb-1">Total Tests Run</div>
            @php
                $totalScenarios = $reportData->sum('total_scenario');
            @endphp
            <h2 class="display-5 fw-bold mb-0">{{ $totalScenarios }}</h2>
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card bg-success text-white shadow-sm h-100">
        <div class="card-body py-2 px-3">
            <div class="medium mb-1">Total Passed Tests</div>
            @php
                $totalPassedScenarios = $reportData->sum('total_passed_scenario');
            @endphp
            <h2 class="display-5 fw-bold mb-0">{{ $totalPassedScenarios }}</h2>
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card bg-danger text-white shadow-sm h-100">
        <div class="card-body py-2 px-3">
            <div class="medium mb-1">Total Failed Tests</div>
            @php
                $totalFailedScenarios = $reportData->sum('total_failed_scenario');
            @endphp
            <h2 class="display-5 fw-bold mb-0">{{ $totalFailedScenarios }}</h2>
        </div>
    </div>
</div>

<div class="col-md-3">
    <div class="card bg-info text-white shadow-sm h-100">
        <div class="card-body py-2 px-3">
            <div class="medium mb-1">Average Success Rate</div>
            @php
                $totalScenarios = $reportData->sum('total_scenario');
                $totalPassedScenarios = $reportData->sum('total_passed_scenario');
                $successRate = $totalScenarios > 0 ? round(($totalPassedScenarios / $totalScenarios) * 100, 2) : null;
            @endphp
            <h2 class="display-5 fw-bold mb-0">
                @if(is_null($successRate))
                    N/A
                @else
                    {{ $successRate }}%
                @endif
            </h2>
        </div>
    </div>
</div>
@endsection

@section('execution_summary')
<div class="row">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="table-responsive">
            <table class="table table-sm border-bottom">
                <thead class="table-light">
                    <tr>
                        <th>Execution Detail</th>
                        <th class="text-end">Value</th>
                        <th class="text-end">Trend</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Avg. Execution Time</td>
                        <td class="text-end">
                            @if($reportData->avg('execution_time'))
                                {{ format_execution_time($reportData->avg('execution_time')) }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="text-end">
                            @php
                                // Calculate trend by comparing to previous period
                                $currentAvg = $reportData->avg('execution_time') ?: 0;
                                
                                // Get previous period data (same time range but shifted back)
                                $previousStartDate = (new DateTime($startDate))->modify('-' . 
                                    ((new DateTime($endDate))->diff(new DateTime($startDate))->days + 1) . ' days')->format('Y-m-d H:i:s');
                                $previousEndDate = (new DateTime($startDate))->modify('-1 day')->format('Y-m-d H:i:s');
                                
                                // Determine if trend data is available
                                $hasTrend = !empty($reportData) && $reportData->count() > 1;
                            @endphp
                            
                            @if($hasTrend)
                                @if($currentAvg > 0)
                                    @php
                                        // Simplified trend - compare current with earlier executions
                                        $sortedData = $reportData->sortBy('date');
                                        $halfPoint = floor($sortedData->count() / 2);
                                        $earlierData = $sortedData->take($halfPoint);
                                        $laterData = $sortedData->slice($halfPoint);
                                        
                                        $earlierAvg = $earlierData->avg('execution_time') ?: 0;
                                        $laterAvg = $laterData->avg('execution_time') ?: 0;
                                        
                                        $trendPct = $earlierAvg > 0 ? 
                                            round((($laterAvg - $earlierAvg) / $earlierAvg) * 100, 1) : 0;
                                    @endphp
                                    
                                    @if($laterAvg < $earlierAvg && abs($trendPct) > 1)
                                        <span class="text-success"><i class="bi bi-arrow-down"></i> {{ abs($trendPct) }}%</span>
                                    @elseif($laterAvg > $earlierAvg && abs($trendPct) > 1)
                                        <span class="text-danger"><i class="bi bi-arrow-up"></i> {{ abs($trendPct) }}%</span>
                                    @else
                                        <span class="text-muted"><i class="bi bi-dash"></i> stable</span>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            @else
                                <span class="text-muted">insufficient data</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Latest Execution</td>
                        <td class="text-end">{{ $reportData->count() > 0 ? date('Y-m-d H:i:s', strtotime($reportData->sortByDesc('date')->first()->date)) : 'N/A' }}</td>
                        <td class="text-end">
                            @if($reportData->count() > 0)
                                @php
                                    $latestDate = strtotime($reportData->sortByDesc('date')->first()->date);
                                    $daysSince = floor((time() - $latestDate) / 86400);
                                @endphp
                                
                                @if($daysSince === 0)
                                    <span class="badge bg-success">Today</span>
                                @elseif($daysSince === 1)
                                    <span class="badge bg-primary">Yesterday</span>
                                @elseif($daysSince <= 7)
                                    <span class="badge bg-warning text-dark">{{ $daysSince }} days ago</span>
                                @else
                                    <span class="badge bg-danger">{{ $daysSince }} days ago</span>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Test Coverage</td>
                        <td class="text-end">{{ $reportData->sum('total_scenario') }} scenarios</td>
                        <td class="text-end">
                            @php
                                if($hasTrend) {
                                    $sortedData = $reportData->sortBy('date');
                                    $halfPoint = floor($sortedData->count() / 2);
                                    $earlierData = $sortedData->take($halfPoint);
                                    $laterData = $sortedData->slice($halfPoint);
                                    
                                    $earlierSum = $earlierData->sum('total_scenario');
                                    $laterSum = $laterData->sum('total_scenario');
                                    
                                    $coverageTrend = $earlierSum > 0 ? 
                                        round((($laterSum - $earlierSum) / $earlierSum) * 100, 1) : 0;
                                }
                            @endphp
                            
                            @if($hasTrend && isset($coverageTrend))
                                @if($coverageTrend > 1)
                                    <span class="text-success"><i class="bi bi-arrow-up"></i> {{ $coverageTrend }}%</span>
                                @elseif($coverageTrend < -1)
                                    <span class="text-danger"><i class="bi bi-arrow-down"></i> {{ abs($coverageTrend) }}%</span>
                                @else
                                    <span class="text-muted"><i class="bi bi-dash"></i> stable</span>
                                @endif
                            @else
                                <span class="text-muted">insufficient data</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <div class="d-flex h-100 flex-column justify-content-center">
            @php
                $totalFailures = $reportData->where('test_status', '!=', 'passed')->count();
                $highestFailingApp = $reportData->where('test_status', '!=', 'passed')
                    ->groupBy('squad_name')
                    ->map(function ($group) {
                        return ['count' => $group->count(), 'name' => $group->first()->squad_name];
                    })
                    ->sortByDesc('count')
                    ->first();
                        
                // Additional data for health status context
                $criticalIssues = [];
                
                // Check for consistently failing apps
                $consistentlyFailing = [];
                foreach ($reportData->where('test_status', '!=', 'passed')->groupBy('squad_name') as $appName => $failedReports) {
                    $totalForThisApp = $reportData->where('squad_name', $appName)->count();
                    $failRate = ($failedReports->count() / $totalForThisApp) * 100;
                    
                    if ($failRate >= 80 && $failedReports->count() >= 2) {
                        $consistentlyFailing[] = [
                            'name' => $appName,
                            'fail_rate' => round($failRate),
                            'count' => $failedReports->count()
                        ];
                    }
                }
                
                // If there are consistently failing apps, add to critical issues
                if (count($consistentlyFailing) > 0) {
                    foreach ($consistentlyFailing as $failing) {
                        $criticalIssues[] = "{$failing['name']} ({$failing['fail_rate']}% failure rate)";
                    }
                }
                
                // Check for regression (previously passing now failing)
                $recentlyRegressed = [];
                foreach ($reportData->groupBy('squad_name') as $appName => $appReports) {
                    if ($appReports->count() >= 2) {
                        $sortedReports = $appReports->sortByDesc('date')->values();
                        if (strtolower($sortedReports[0]->test_status) !== 'passed' && 
                            strtolower($sortedReports[1]->test_status) === 'passed') {
                            $recentlyRegressed[] = $appName;
                        }
                    }
                }
                
                // If there are regressions, add to critical issues
                if (count($recentlyRegressed) > 0) {
                    foreach ($recentlyRegressed as $appName) {
                        $criticalIssues[] = "$appName (recently regressed)";
                    }
                }
            @endphp
            <div class="d-flex justify-content-between mb-2">
                <span>Total Failures</span>
                <span class="badge bg-danger">{{ $totalFailures }}</span>
            </div>
            @if($highestFailingApp)
            <div class="d-flex justify-content-between mb-2">
                <span>Highest Failing App</span>
                <span class="badge bg-warning text-dark">{{ $highestFailingApp['name'] }}</span>
            </div>
            @endif
            <div class="d-flex justify-content-between mb-2">
                <span>Health Status</span>
                <span class="badge bg-{{ $successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning text-dark' : 'danger') }}">
                    {{ $successRate >= 90 ? 'Healthy' : ($successRate >= 70 ? 'Warning' : 'Critical') }}
                </span>
            </div>
            
            @if($successRate < 90)
            <div class="mt-2">
                <small class="text-{{ $successRate < 70 ? 'danger' : 'warning' }}">
                    <strong>Issues affecting health:</strong>
                    <ul class="mb-0 ps-3 mt-1">
                        @if(count($criticalIssues) > 0)
                            @foreach($criticalIssues as $issue)
                                <li>{{ $issue }}</li>
                            @endforeach
                        @else
                            @if($highestFailingApp)
                                <li>{{ $highestFailingApp['name'] }} ({{ $highestFailingApp['count'] }} failures)</li>
                            @endif
                            <li>Overall success rate below threshold ({{ $successRate }}%)</li>
                        @endif
                    </ul>
                </small>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('data_table')
<table class="table table-sm table-striped table-hover mb-0">
    <thead class="table-light">
        <tr>
            <th class="ps-3 sortable" data-sort="id">
                ID <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
            <th class="sortable" data-sort="squad_name">
                App <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
            <th class="sortable" data-sort="env">
                Environment <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
            <th class="sortable" data-sort="test_status">
                Status <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
            <th class="sortable" data-sort="execution_time">
                Execution Time <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
            <th class="sortable" data-sort="total_scenario">
                Total <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
            <th class="sortable" data-sort="total_passed_scenario">
                Passed <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
            <th class="sortable" data-sort="total_failed_scenario">
                Failed <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
            <th class="sortable" data-sort="success_rate">
                Success Rate <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
            <th class="sortable">
                Trend <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
            <th class="pe-3 sortable" data-sort="date">
                Date <i class="bi bi-arrow-down-up sort-icon ms-1"></i>
            </th>
        </tr>
    </thead>
    <tbody>
        @if(count($reportData) > 0)
            @foreach($paginatedData as $index => $report)
                @php
                    // Get previous report for the same app (if exists)
                    $prevReport = null;
                    $prevSuccessRate = null;
                    $rateChange = null;
                    $executionTimeChange = null;
                    
                    // Find the previous report for the same app in the full dataset
                    foreach ($reportData as $r) {
                        if ($r->squad_name === $report->squad_name && $r->env === $report->env && $r->date < $report->date) {
                            $prevReport = $r;
                            break;
                        }
                    }
                    
                    // Calculate trend if previous report exists
                    if ($prevReport) {
                        $prevSuccessRate = $prevReport->success_rate;
                        $rateChange = $report->success_rate - $prevSuccessRate;
                        
                        // Calculate execution time change
                        if ($prevReport->execution_time > 0 && $report->execution_time > 0) {
                            $executionTimeChange = $report->execution_time - $prevReport->execution_time;
                            $executionTimeChangePct = $prevReport->execution_time > 0 
                                ? round(($executionTimeChange / $prevReport->execution_time) * 100, 1) 
                                : 0;
                        }
                    }
                @endphp
                <tr>
                    <td class="ps-3">{{ $report->id }}</td>
                    <td class="fw-medium">{{ $report->squad_name }}</td>
                    <td>{{ $report->env }}</td>
                    <td>
                        <span class="badge rounded-pill bg-{{ strtolower($report->test_status) === 'passed' ? 'success' : 'danger' }}">
                            {{ $report->test_status }}
                        </span>
                    </td>
                    <td>
                        {{ format_execution_time($report->execution_time) }}
                        @if($executionTimeChange)
                            <small class="{{ $executionTimeChange < 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bi bi-arrow-{{ $executionTimeChange < 0 ? 'down' : 'up' }}"></i>
                                {{ abs($executionTimeChangePct) }}%
                            </small>
                        @endif
                    </td>
                    <td>{{ $report->total_scenario }}</td>
                    <td class="text-success">{{ $report->total_passed_scenario }}</td>
                    <td class="text-danger">{{ $report->total_failed_scenario }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2 dashboard-progress">
                                @php
                                    $successRate = round(($report->success_rate) * 100, 2) ?: 0;
                                @endphp
                                <div class="progress-bar bg-{{ $successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning text-dark' : 'danger') }}" 
                                    role="progressbar" 
                                    style="width: {{ $successRate }}%;" 
                                    aria-valuenow="{{ $successRate }}" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                </div>
                            </div>
                            <span class="small">{{ number_format($successRate, 2) }}%</span>
                        </div>
                    </td>
                    <td>
                        @if($rateChange !== null)
                            <span class="{{ $rateChange > 0 ? 'text-success' : ($rateChange < 0 ? 'text-danger' : 'text-muted') }}">
                                @if($rateChange > 0)
                                    <i class="bi bi-arrow-up-right"></i> +{{ number_format(abs($rateChange), 1) }}%
                                @elseif($rateChange < 0)
                                    <i class="bi bi-arrow-down-right"></i> -{{ number_format(abs($rateChange), 1) }}%
                                @else
                                    <i class="bi bi-dash"></i> stable
                                @endif
                            </span>
                        @else
                            <span class="text-muted"><i class="bi bi-dash"></i></span>
                        @endif
                    </td>
                    <td class="pe-3">{{ date('Y-m-d', strtotime($report->date)) }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="11" class="text-center py-3">No data available for the selected filters.</td>
            </tr>
        @endif
    </tbody>
</table>
@endsection 