@extends('layout.base_layout')

@section('title', $title ?? 'Dashboard')

@section('content')
<!-- <link href="{{ asset_path('css/dashboard.css') }}" rel="stylesheet"> -->
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css" rel="stylesheet"> -->
<link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">

<div class="container-fluid p-3 main-content">
    <div class="row mb-3">
        <div class="col">
            <h2 class="page-title">{{ $title ?? 'Dashboard' }}</h2>
            <p class="text-muted mb-0">{{ $subtitle ?? 'Monitor and analyze automated tests' }}</p>
        </div>
    </div>

    <!-- Grafana-style Filter Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <form action="{{ $filterAction }}" method="GET" id="dashboard-filters" class="d-flex align-items-center bg-dark p-2 rounded">
                <!-- Filter Component -->
                @if(isset($filterOptions))
                <div class="me-2">
                    <label for="{{ $filterOptions['id'] }}" class="text-white me-1 small mb-0">{{ $filterOptions['label'] }}</label>
                    <div class="d-inline-block">
                        <select name="{{ $filterOptions['name'] }}" id="{{ $filterOptions['id'] }}" class="form-select form-select-sm border-secondary" onchange="this.form.submit()">
                            <option value="">All</option>
                            @foreach($filterOptions['items'] as $item)
                                <option value="{{ $item }}" {{ $filterOptions['selected'] == $item ? 'selected' : '' }}>
                                    {{ $item }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <!-- Environment Filter Component -->
                @if(isset($environmentOptions))
                <div class="me-2">
                    <label for="environment" class="text-white me-1 small mb-0">Environment</label>
                    <div class="d-inline-block">
                        <select name="environment" id="environment" class="form-select form-select-sm border-secondary">
                            @foreach($environmentOptions['items'] as $env)
                                <option value="{{ $env }}" {{ $environmentOptions['selected'] == $env ? 'selected' : '' }}>
                                    {{ $env === 'uat' ? strtoupper($env) : ucfirst($env) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <!-- Time Range Selection -->
                <div class="ms-auto d-inline-block">
                    <div class="d-flex align-items-center bg-dark rounded p-2">
                        <div class="d-flex align-items-center me-3">
                            <label for="start_date" class="text-white me-2 small mb-0">From:</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control border-secondary date-input" 
                                       id="start_date" name="start_date" 
                                       value="{{ date('Y-m-d', strtotime($startDate)) }}">
                            </div>
                        </div>
                        <div class="d-flex align-items-center me-3">
                            <label for="end_date" class="text-white me-2 small mb-0">To:</label>
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control border-secondary date-input" 
                                       id="end_date" name="end_date" 
                                       value="{{ date('Y-m-d', strtotime($endDate)) }}">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex">
                                <button type="submit" class="btn btn-primary btn-sm me-2">
                                    <i class="bi bi-filter me-1"></i>Apply Filter
                                </button>
                                
                                <button type="submit" name="refresh_cache" value="1" class="btn btn-secondary btn-sm me-2" title="Refresh data from database">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                                </button>
                            </div>
                            
                            @if(isset($dateDebug['from_cache']) && $dateDebug['from_cache'])
                            <span class="ms-2 small text-muted align-self-center" title="Data is loaded from cache">
                                <i class="bi bi-database-check text-success"></i> Cached
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row mb-3 g-3">
        @yield('dashboard_cards')
    </div>

    <!-- Execution Summary Section -->
    <div class="row mb-3 g-3">
        <div class="col-12">
            @if(request()->has('debug'))
                @include('partials.debug_info')
            @endif
            
            @if(isset($dateDebug['warning']))
            <div class="alert alert-warning mb-3">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Warning:</strong> {{ $dateDebug['warning'] }}
                <p class="small mb-0 mt-1">Consider narrowing your search by selecting a specific item or reducing the date range.</p>
            </div>
            @endif
            
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white py-2 px-3 d-flex align-items-center">
                    <h6 class="card-title mb-0">Execution Summary</h6>
                    <span class="ms-auto badge bg-primary">{{ $selectedEnvironment }}</span>
                </div>
                <div class="card-body">
                    @yield('execution_summary')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">Test Results</h6>
            <div class="d-flex align-items-center">
                <span class="me-3 small text-black">Show</span>
                <form method="GET" action="{{ request()->url() }}" class="m-0">
                    @foreach(request()->except('per_page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <div class="position-relative page-size-select-wrapper">
                        <select id="page-size-select" name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" {{ $perPage == $size ? 'selected' : '' }}>{{ $size }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                @if(isset($dateDebug['from_cache']) && $dateDebug['from_cache'] && isset($dateDebug['cache_created_at']))
                <div class="alert alert-info border-0 rounded-0 m-0 py-2">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <strong>Cached Data:</strong> Showing cached results 
                                <a href="?{{ http_build_query(array_merge(request()->except('refresh_cache'), ['refresh_cache' => 1])) }}" class="ms-2 text-decoration-none">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh Now
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                @yield('data_table')
            </div>
        </div>
        @if(count($reportData) > 0)
        <div class="card-footer bg-dark d-flex justify-content-between align-items-center py-2">
            <div class="small text-white">
                Showing {{ $paginatedData->firstItem() ?? 0 }} to {{ $paginatedData->lastItem() ?? 0 }} of {{ $paginatedData->total() }} entries
                @if(isset($dateDebug['from_cache']) && $dateDebug['from_cache'] && isset($dateDebug['cache_created_at']))
                <span class="ms-2">
                    <i class="bi bi-database-check text-info"></i> 
                    Cached results
                </span>
                @endif
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3 small text-white">Page {{ $paginatedData->currentPage() }} of {{ $paginatedData->lastPage() }}</span>
                <nav aria-label="Table navigation">
                    <ul class="pagination pagination-sm mb-0">
                        @php
                            $prevPageUrl = $paginatedData->onFirstPage() ? '#' : 
                                request()->fullUrlWithQuery(['page' => $paginatedData->currentPage() - 1, 'per_page' => $perPage]);
                            $nextPageUrl = $paginatedData->hasMorePages() ? 
                                request()->fullUrlWithQuery(['page' => $paginatedData->currentPage() + 1, 'per_page' => $perPage]) : '#';
                        @endphp
                        <li class="page-item {{ $paginatedData->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link pagination-link" href="{{ $prevPageUrl }}" aria-label="Previous" data-page="{{ $paginatedData->currentPage() - 1 }}">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item {{ $paginatedData->hasMorePages() ? '' : 'disabled' }}">
                            <a class="page-link pagination-link" href="{{ $nextPageUrl }}" aria-label="Next" data-page="{{ $paginatedData->currentPage() + 1 }}">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="{{ asset_path('js/dashboard_analytic.js') }}"></script>
@endsection 