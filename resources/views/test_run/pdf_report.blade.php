<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ui.test_run_report') }} - {{ $testRun->title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        
        .header h2 {
            color: #7f8c8d;
            margin: 5px 0;
            font-size: 16px;
            font-weight: normal;
        }
        
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 150px;
            font-weight: bold;
            padding: 5px 10px 5px 0;
            vertical-align: top;
        }
        
        .info-value {
            display: table-cell;
            padding: 5px 0;
            vertical-align: top;
        }
        
        .summary {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 30px;
        }
        
        .summary h3 {
            margin-top: 0;
            color: #495057;
        }
        
        .stats {
            display: table;
            width: 100%;
        }
        
        .stat-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            background-color: #fff;
        }
        
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            display: block;
        }
        
        .stat-label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .passed { color: #28a745; }
        .failed { color: #dc3545; }
        .blocked { color: #ffc107; }
        .not-tested { color: #6c757d; }
        
        .test-cases {
            margin-top: 30px;
        }
        
        .test-cases h3 {
            color: #495057;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        
        .test-case-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .test-case-table th,
        .test-case-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        
        .test-case-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            font-size: 11px;
        }
        
        .test-case-table td {
            font-size: 10px;
        }
        
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: white;
        }
        
        .status-passed { background-color: #28a745; }
        .status-failed { background-color: #dc3545; }
        .status-blocked { background-color: #ffc107; color: #212529; }
        .status-not-tested { background-color: #6c757d; }
        
        .footer {
            position: fixed;
            bottom: 20px;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('ui.test_run_report') }}</h1>
        <h2>{{ $testRun->title }}</h2>
        <p>{{ __('ui.generated_on') }} {{ $generatedAt }}</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">{{ __('ui.project') }}:</div>
            <div class="info-value">{{ $project->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('ui.test_run_id') }}:</div>
            <div class="info-value">#{{ $testRun->id }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('ui.test_plan') }}:</div>
            <div class="info-value">{{ $testPlan->title }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('ui.repository') }}:</div>
            <div class="info-value">{{ $repository->title }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('ui.created_at') }}:</div>
            <div class="info-value">{{ $testRun->created_at->format(__('ui.datetime_full_format')) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('ui.total_test_cases') }}:</div>
            <div class="info-value">{{ $totalCases }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">{{ __('ui.executed_by') }}:</div>
            <div class="info-value">
                @if($executor)
                    {{ $executor->name }} ({{ $executor->email }})
                @else
                    {{ __('ui.unknown_user') }}
                @endif
            </div>
        </div>
    </div>

    <div class="summary">
        <h3>{{ __('ui.test_execution_summary') }}</h3>
        <div class="stats">
            <div class="stat-item">
                <span class="stat-number passed">{{ $chartData['passed'][0] }}</span>
                <span class="stat-label">{{ __('ui.passed') }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-number failed">{{ $chartData['failed'][0] }}</span>
                <span class="stat-label">{{ __('ui.failed') }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-number blocked">{{ $chartData['blocked'][0] }}</span>
                <span class="stat-label">{{ __('ui.blocked') }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-number not-tested">{{ $chartData['not_tested'][0] }}</span>
                <span class="stat-label">{{ __('ui.not_tested') }}</span>
            </div>
        </div>
        
        <div style="margin-top: 15px;">
            <strong>{{ __('ui.success_rate') }}:</strong> 
            @if($totalCases > 0)
                {{ number_format(($chartData['passed'][0] / $totalCases) * 100, 1) }}%
            @else
                0%
            @endif
        </div>
    </div>

    <div class="test-cases">
        <h3>{{ __('ui.detailed_results') }}</h3>
        
        <table class="test-case-table">
            <thead>
                <tr>
                    <th>{{ __('ui.id') }}</th>
                    <th>{{ __('ui.test_case') }}</th>
                    <th>{{ __('ui.suite') }}</th>
                    <th>{{ __('ui.priority') }}</th>
                    <th>{{ __('ui.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($testCases as $testCase)
                <tr>
                    <td>{{ $testCase['id'] }}</td>
                    <td>{{ $testCase['title'] }}</td>
                    <td>{{ $testCase['suite_name'] }}</td>
                    <td>{{ $testCase['priority'] }}</td>
                    <td>
                        @php
                            $statusKey = strtolower($testCase['status']);
                            $statusText = __('ui.' . $statusKey);
                        @endphp
                        <span class="status-badge status-{{ strtolower(str_replace('_', '-', $testCase['status'])) }}">
                            {{ $statusText }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>QaraTMS - Test Management System | {{ __('ui.page') }} <span class="pagenum"></span></p>
    </div>
</body>
</html>