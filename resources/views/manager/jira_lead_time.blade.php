@extends('layout.base_layout')

@section('title', 'Jira Lead Time')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Jira Issue Lead Time</h1>
                <p class="text-muted">Track time from issue creation to resolution across projects</p>
                <div class="badge bg-success mb-2">Last synchronized on {{$latestUpdatedAtGMT7}}. Data has been persisted to the database.</div>
            </div>
            <a href="{{ route('manager.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- Filter Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <label for="date-range" class="form-label">Time Period</label>
                            <select name="date_range" id="date-range" class="form-select">
                                <option value="last-month" {{ request('date_range') == 'last-month' ? 'selected' : '' }}>Last Month</option>
                                <option value="last-3-months" {{ request('date_range') == 'last-3-months' ? 'selected' : '' }}>Last 3 Months</option>
                                <option value="last-6-months" {{ request('date_range') == 'last-6-months' ? 'selected' : '' }}>Last 6 Months</option>
                                <option value="last-year" {{ request('date_range') == 'last-year' ? 'selected' : '' }}>Last Year</option>
                                <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>

                        
                        <div class="col-md-6 custom-date-range" id="custom-date-row">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" 
                                        value="{{ request('start_date') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" 
                                        value="{{ request('end_date') }}">
                                </div>
                                <div class="col-12">
                                    <span id="date-error" class="text-danger" style="display:none;">Start date cannot be after end date.</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="project" class="form-label">Jira Project</label>
                            <select id="project" name="project_name" class="form-select">
                                <option value="all" {{ request('project_name') == 'all' ? 'selected' : '' }}>All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project }}" {{ request('project_name') == $project ? 'selected' : '' }}>{{ $project }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="issue-type" class="form-label">Issue Type</label>
                            <select id="issue-type" name="issue_type" class="form-select">
                                <option value="all" {{ request('issue_type') == 'all' ? 'selected' : '' }}>All Types</option>
                                @foreach($issueTypes as $issueType)
                                    <option value="{{ $issueType }}" {{ request('issue_type') == $issueType ? 'selected' : '' }}>{{ $issueType }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end" style="margin-bottom: -2rem !important;">
                            <a href="{{ route('manager.jira_lead_time') }}" class="btn btn-secondary w-100 me-2">
                                <i class="bi bi-x-circle me-1"></i> Reset
                            </a>
                            <button type="submit" class="btn btn-primary w-100 me-2">
                                <i class="bi bi-filter me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">Avg. Lead Time</h6>
                            <h3 class="fw-bold mb-0">{{ $avgLeadTime }} Days</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-hourglass-split text-primary fs-4"></i>
                        </div>
                    </div>
                    <!-- <div class="mt-3">
                        <span class="badge bg-success"><i class="bi bi-arrow-down me-1"></i>8.2%</span>
                        <span class="text-muted ms-2">from last period</span>
                    </div> -->
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">Total Issues</h6>
                            <h3 class="fw-bold mb-0">{{ $totalIssue }}</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-journal-text text-success fs-4"></i>
                        </div>
                    </div>
                    <!-- <div class="mt-3">
                        <span class="badge bg-success"><i class="bi bi-arrow-up me-1"></i>12.4%</span>
                        <span class="text-muted ms-2">from last period</span>
                    </div> -->
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">Completion Rate</h6>
                            <h3 class="fw-bold mb-0">{{ $completeRate }}%</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-check2-circle text-info fs-4"></i>
                        </div>
                    </div>
                    <!-- <div class="mt-3">
                        <span class="badge bg-success"><i class="bi bi-arrow-up me-1"></i>4.7%</span>
                        <span class="text-muted ms-2">from last period</span>
                    </div> -->
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">Blocked Rate</h6>
                            <h3 class="fw-bold mb-0">{{ $blockedRate }}%</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-exclamation-octagon text-warning fs-4"></i>
                        </div>
                    </div>
                    <!-- <div class="mt-3">
                        <span class="badge bg-success"><i class="bi bi-arrow-down me-1"></i>3.1%</span>
                        <span class="text-muted ms-2">from last period</span>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lead Time Trend Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Lead Time Trend by Issue Type</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:700px;">
                        <canvas id="jiraLeadTimeTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lead Time by Issue Type and Project -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Lead Time by Issue Type</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="issueTypeLeadTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Lead Time by Project</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="projectLeadTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Jira Issues -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Jira Issues</h5>
                    <!-- <a href="#" class="btn btn-sm btn-outline-primary">View All Issues</a> -->
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Key</th>
                                    <th>Summary</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Assignee</th>
                                    <th>Lead Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($responses as $response)
                                    <tr>
                                        <td><a href="https://admin.atlassian.net/browse/{{ $response['jira_key'] }}" class="text-primary">{{ $response['jira_key'] }}</a></td>
                                        <td style="max-width: 560px;">{{ $response['summary'] }}</td>
                                        <td><span class="badge bg-primary">{{ $response['issue_type'] }}</span></td>
                                        <td><span class="badge bg-success">{{ $response['issue_status'] }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2" 
                                                    style="width: 30px; height: 30px; font-size: 12px;">
                                                    @php
                                                        $words = explode(" ", $response['assignee']);
                                                        $acronym = "";

                                                        foreach ($words as $w) {
                                                            $acronym .= mb_substr($w, 0, 1);
                                                        }
                                                    @endphp
                                                    {{ $acronym }}
                                                </span>
                                                <span>{{ $response['assignee'] }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $response['lead_time'] }} days</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- <div class="card-footer bg-light">
                    <nav aria-label="Issues pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div> -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
    <script src="{{ asset_path('js/jira_lead_time.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.chartLabels = @json($labels);
        window.chartDatasets = @json($datasets);
        window.chartTrendData = @json($trendData);
        window.chartProjectData = @json($projectDatasets);
    </script>
@endsection
