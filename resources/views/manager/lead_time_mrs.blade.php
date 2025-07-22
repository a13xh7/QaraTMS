@extends('layout.base_layout')

@section('title', 'Lead Time MRs')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Merge Request Lead Time</h1>
                <p class="text-muted">Track lead time for merge requests from creation to deployment excluding weekends</p>
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
                <div class="card-body mb-4">
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
                            <label for="author" class="form-label">Author</label>
                            <select id="author" name="author_name" class="form-select">
                                <option value="all" {{ request('author_name') == 'all' ? 'selected' : '' }}>All</option>
                                @foreach($authors as $author)
                                    <option value="{{ $author }}" {{ request('author_name') == $author ? 'selected' : '' }}>{{ $author }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="project" class="form-label">Project</label>
                            <select id="project" name="project_name" class="form-select">
                                <option value="all" {{ request('project_name') == 'all' ? 'selected' : '' }}>All</option>
                                @foreach($repositories as $repo)
                                    @php
                                        $project = basename($repo);
                                    @endphp
                                    <option value="{{ $project }}" {{ request('project_name') == $author ? 'selected' : '' }}>{{ $project }}</option>
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
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">Avg. Lead Time (Excluding Weekends)</h6>
                            <h3 class="fw-bold mb-0">{{ $avgLeadTimeDays }} Days</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-hourglass-split text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-info">{{ $avgLeadTimeHours }} Hours</span>
                        <span class="text-muted ms-2">total average time</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">Total MRs</h6>
                            <h3 class="fw-bold mb-0">{{ $totalMRs }}</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-git text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-warning text-dark"> {{ $avgTotalMrPerAuthor }} MR</span>
                        <span class="text-muted ms-2">avg per author</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">First Commit to Merge</h6>
                            <h3 class="fw-bold mb-0">
                                {{ $avgFirstCommitToMergeDays }} Days
                            </h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-clock-history text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-warning text-dark">{{$avgFirstCommitToMergeHours}} Hours</span>
                        <span class="text-muted ms-2">avg from first commit to merge</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lead Time Trend Chart -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light py-2">
                    <h5 class="card-title mb-0">Merge Request Lead Time Trend</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container bg-white" data-chart="trend" style="position: relative; height: 400px;">
                        <canvas id="leadTimeTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Project Comparison -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light py-2">
                    <h5 class="card-title mb-0">Lead Time by Project</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container bg-white" data-chart="project" style="position: relative; height: 400px;">
                        <canvas id="projectLeadTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Author Analysis -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Author Analysis</h5>
                    <span class="badge bg-info">Top Contributors</span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="chart-container bg-white" data-chart="author" id="authorChartContainer" style="position: relative; height: 400px;">
                                <canvas id="authorMergeCountChart" style="position: relative; height: 400px;"></canvas>
                                <div id="authorZoomIcon" class="zoom-icon-overlay" style="cursor:pointer;">
                                    <i class="bi bi-zoom-in fs-4 text-muted"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-container bg-white" data-chart="author" id="authorAvgChartContainer" style="position: relative; height: 400px;">
                                <canvas id="authorAvgMergeCountChart" style="position: relative; height: 400px;"></canvas>
                                <div id="authorAvgZoomIcon" class="zoom-icon-overlay" style="cursor:pointer;">
                                    <i class="bi bi-zoom-in fs-4 text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Project Detailed Analysis -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light py-2">
                    <h5 class="card-title mb-0">Project Detailed Analysis</h5>
                </div>
                <div class="card-body">
                    @if(empty($mergeRequests))
                    <div class="alert alert-info mb-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <div>
                                <h5 class="alert-heading">No Data Available</h5>
                                <p class="mb-0">Click the "Advanced Fetch" button to fetch GitLab merge request data for analysis.</p>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="chart-container bg-white" data-chart="project" style="position: relative; height: 200px;">
                        <canvas id="projectDetailedChart"></canvas>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Merge Requests Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Merge Requests</h5>
                    <span class="badge bg-primary">{{ $totalMRs }} MRs</span>
                </div>
                <div class="overflow-auto">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>MR ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Created At</th>
                                <th>Merged At</th>
                                <th>Time to Merge<br>(Days)</th>
                                <th>Labels</th>
                                <th>Repository</th>
                                <th>URL</th>
                                <th>Time to Merge<br>(Hours)</th>
                                <th>Time from First Commit<br>(Days)</th>
                                <th>Time from First Commit<br>(Hours)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mrs as $mr)
                            <tr>
                                <td>{{ $mr['mr_id'] }}</td>
                                <td>{{ Str::limit($mr['title'], 40) }}</td>
                                <td>{{ $mr['author'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($mr['mr_created_at'])->format('Y-m-d H:i:s') }}</td>
                                <td>{{ \Carbon\Carbon::parse($mr['mr_merged_at'])->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $mr['time_to_merge_days'] }}</td>
                                <td>{{ Str::limit($mr['labels'], 10) }}</td>
                                <td>{{ basename($mr['repository']) }}</td>
                                <td><a href="{{ $mr['url'] }}" target="_blank" class="text-primary">View MR</a></td>
                                <td>{{ $mr['time_to_merge_hours'] }}</td>
                                <td>{{ $mr['first_commit_to_merge_days'] ?? '-' }}</td>
                                <td>{{ $mr['first_commit_to_merge_hours'] ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-info-circle me-2"></i>No merge requests found for the selected filters
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="authorChartModal" tabindex="-1" aria-labelledby="authorChartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authorChartModalLabel">MR Count per Author (Large View)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="overflow-x: auto;">
                <canvas id="authorMergeCountChartLarge" style="width:100%; height:400px; min-width: 800px;"></canvas>
            </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="authorAvgChartModal" tabindex="-1" aria-labelledby="authorAvgChartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="authorAvgChartModalLabel">MR Lead Time per Author (Large View)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="overflow-x: auto;">
                <canvas id="authorMergeAvgChartLarge" style="width:100%; height:400px; min-width: 800px;"></canvas>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
    <script src="{{ asset_path('js/lead_time_mrs.js') }}"></script>

    <script>
        window.chartData = {!! json_encode($chartData ?? null) !!};
    </script>
@endsection
