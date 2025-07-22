@extends('layout.base_layout')

@section('title', 'Monthly Contribution')

@section('head')
<!-- Chart.js for visualizations -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<!-- Advanced analytics CSS -->
<link rel="stylesheet" href="{{ asset_path('css/advanced-analytics.css') }}">
@endsection

@section('content')
<div class="container-fluid p-4">
    
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Monthly Contribution Analysis</h1>
                <p class="text-muted">Track team and individual code contributions over time</p>
                <div class="badge bg-success mb-2">Last synchronized on {{$latestUpdatedAtGMT7}}. Data has been persisted to the database.</div>
            </div>
            <div>
                <button id="advancedAnalysisBtn" class="btn btn-outline-primary me-2" aria-label="Open advanced analysis panel">
                    <i class="bi bi-graph-up-arrow me-1"></i> Advanced Analysis
                </button>
                <a href="{{ route('manager.index') }}" class="btn btn-outline-secondary" aria-label="Return to dashboard">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <!-- Filter Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form id="filterForm" class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <label for="year" class="form-label">Year</label>
                            <select id="year" name="year" class="form-select">
                                <option value="all">All Years</option>
                                @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="month" class="form-label">Month</label>
                            <select id="month" name="month" class="form-select">
                                <option value="all" {{ !isset($selectedMonth) || $selectedMonth === 'all' ? 'selected' : '' }}>All Months</option>
                                @foreach($availableMonth as $monthNum)
                                    <option value="{{ $monthNum }}" {{ request('month') == $monthNum ? 'selected' : '' }}>{{ $monthNum }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" 
                                aria-label="Apply selected filters"
                                style="margin-top: 1.85rem;">
                                <i class="bi bi-filter me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Total Contributions</h5>
                        <i class="bi bi-bar-chart-line text-primary fs-4" aria-hidden="true"></i>
                    </div>
                    <h2 id="totalContributions" class="display-6 fw-bold mb-0">{{$totalEvent}}</h2>
                    <p class="text-muted mb-0">All activities</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Total MRs Created</h5>
                        <i class="bi bi-git text-success fs-4" aria-hidden="true"></i>
                    </div>
                    <h2 id="totalMRsCreated" class="display-6 fw-bold mb-0">{{$totalMRCreated}}</h2>
                    <p class="text-muted mb-0">Merge requests</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Total MRs Approved</h5>
                        <i class="bi bi-check-circle text-info fs-4" aria-hidden="true"></i>
                    </div>
                    <h2 id="totalMRsApproved" class="display-6 fw-bold mb-0">{{$totalMRApproved}}</h2>
                    <p class="text-muted mb-0">Approvals</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Total Repo Pushes</h5>
                        <i class="bi bi-cloud-arrow-up text-danger fs-4" aria-hidden="true"></i>
                    </div>
                    <h2 id="totalRepoPushes" class="display-6 fw-bold mb-0">{{$totalMRPush}}</h2>
                    <p class="text-muted mb-0">Code pushes</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Chart Section -->
    <div class="row mb-4">
        <div class="col-md-12 col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Monthly Contribution Trends</h5>
                </div>
                <div class="card-body">
                    <noscript>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>Please enable JavaScript to view contribution charts.
                        </div>
                    </noscript>
                    <canvas id="contributionTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Top Contributors</h5>
                </div>
                <div class="card-body">
                    <noscript>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>Please enable JavaScript to view contributor charts.
                        </div>
                    </noscript>
                    <canvas id="topContributorsChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contribution Data Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Contributions Data</h5>
                    <div>
                        <button id="exportCSV" class="btn btn-sm btn-outline-secondary ms-2" aria-label="Export data to CSV">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export CSV
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Search Box -->
                    <div class="p-3 border-bottom">
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="contributorSearch" class="form-control" placeholder="Search by contributor name..." aria-label="Search contributors">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" aria-label="Clear search">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="contributionsTable" class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="sortable" data-sort="year">Year <i class="bi bi-arrow-down-up"></i></th>
                                    <th scope="col" class="sortable" data-sort="monthName">Month <i class="bi bi-arrow-down-up"></i></th>
                                    <th scope="col" class="sortable" data-sort="name">Name <i class="bi bi-arrow-down-up"></i></th>
                                    <th scope="col" class="sortable" data-sort="mrCreated">MR Created <i class="bi bi-arrow-down-up"></i></th>
                                    <th scope="col" class="sortable" data-sort="mrApproved">MR Approved <i class="bi bi-arrow-down-up"></i></th>
                                    <th scope="col" class="sortable" data-sort="repoPushes">Repo Pushes <i class="bi bi-arrow-down-up"></i></th>
                                    <th scope="col" class="sortable" data-sort="totalEvents">Total Events <i class="bi bi-arrow-down-up"></i></th>
                                </tr>
                            </thead>
                            <tbody id="contributionsData">
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Showing <span id="paginationStart">0</span> to <span id="paginationEnd">0</span> of <span id="paginationTotal">0</span> entries
                        </div>
                        <div class="btn-group">
                            <button id="prevPage" class="btn btn-sm btn-outline-secondary" disabled aria-label="Previous page">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button id="nextPage" class="btn btn-sm btn-outline-secondary" disabled aria-label="Next page">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer')
<!-- Load the modular JavaScript -->
<script src="{{ asset_path('js/monthly_contribution.js') }}"></script>
<script src="{{ asset_path('js/charts/contribution-charts.js') }}"></script>
<script src="{{ asset_path('js/charts/advanced-analytics.js') }}"></script>
<script>
    window.topContributionData = {!! json_encode($topContributionData ?? null) !!};
    window.trendData = {!! json_encode($trendData ?? null) !!};
    window.initialData = {!! json_encode($initialData ?? null) !!};
</script>
@endsection
