@extends('layout.base_layout')

@section('head')
    <link rel="stylesheet" href="{{ asset_path('css/project.css') }}">
@endsection

@section('content')
    @include('layout.sidebar_nav')

    <div class="flex-grow-1 main-content">
        <div class="container-fluid px-4 py-4">
            <!-- Header and Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="page-title mb-1">{{ __('Projects') }}</h3>
                    <p class="text-muted mb-0">{{ __('Manage your testing projects and resources') }}</p>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="input-group me-3">
                        <input type="text" class="form-control" id="projectSearch" placeholder="{{ __('Search projects...') }}">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    
                    @can('add_edit_projects')
                        <a href="{{ route('project_create_page') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> {{ __('Create Project') }}
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Project Stats -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-3">
                    <div class="row g-0 text-center">
                        <div class="col-md-3 border-end">
                            <h3 class="mb-0 fw-bold text-primary">{{ count($projects) }}</h3>
                            <p class="text-muted mb-0 small">{{ __('Projects') }}</p>
                        </div>
                        <div class="col-md-3 border-end">
                            <h3 class="mb-0 fw-bold text-primary">{{ $projects->sum(function($p) { return $p->repositoriesCount(); }) }}</h3>
                            <p class="text-muted mb-0 small">{{ __('Repositories') }}</p>
                        </div>
                        <div class="col-md-3 border-end">
                            <h3 class="mb-0 fw-bold text-primary">{{ $projects->sum(function($p) { return $p->testPlansCount(); }) }}</h3>
                            <p class="text-muted mb-0 small">{{ __('Test Plans') }}</p>
                        </div>
                        <div class="col-md-3">
                            <h3 class="mb-0 fw-bold text-primary">{{ $projects->sum(function($p) { return $p->testRunsCount(); }) }}</h3>
                            <p class="text-muted mb-0 small">{{ __('Test Runs') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            @if(count($projects) == 0)
                <div class="text-center py-5 my-4 bg-light rounded">
                    <div class="py-4">
                        <i class="bi bi-folder text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">{{ __('No Projects Found') }}</h4>
                        <p class="text-muted">{{ __('Create your first project to start organizing your testing efforts.') }}</p>
                        
                        @can('add_edit_projects')
                            <a href="{{ route('project_create_page') }}" class="btn btn-primary mt-2">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('Create Project') }}
                            </a>
                        @endcan
                    </div>
                </div>
            @endif

            <!-- Project Grid -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="projectGrid">
                @foreach($projects as $project)
                    <div class="col project-item">
                        <div class="card h-100 border-0 shadow-sm project-card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                <h5 class="mb-0 project-title">
                                    <i class="bi bi-briefcase me-2 text-primary"></i>
                                    {{ $project->title }}
                                </h5>
                                
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('project_show_page', $project->id) }}">
                                                <i class="bi bi-eye me-2"></i> {{ __('View Project') }}
                                            </a>
                                        </li>
                                        @can('add_edit_projects')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('project_edit_page', $project->id) }}">
                                                    <i class="bi bi-pencil me-2"></i> {{ __('Edit Project') }}
                                                </a>
                                            </li>
                                        @endcan
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('repository_list_page', $project->id) }}">
                                                <i class="bi bi-server me-2"></i> {{ __('Repositories') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('test_plan_list_page', $project->id) }}">
                                                <i class="bi bi-journals me-2"></i> {{ __('Test Plans') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('test_run_list_page', $project->id) }}">
                                                <i class="bi bi-play-circle me-2"></i> {{ __('Test Runs') }}
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('project_documents_list_page', $project->id) }}">
                                                <i class="bi bi-file-text-fill me-2"></i> {{ __('Documents') }}
                                            </a>
                                        </li>
                                        @can('delete_projects')
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#deleteModal" 
                                                   data-project-id="{{ $project->id }}"
                                                   data-project-title="{{ $project->title }}">
                                                    <i class="bi bi-trash me-2"></i> {{ __('Delete Project') }}
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="card-body d-flex flex-column">
                                <div class="project-description flex-grow-1 mb-3">
                                    @if($project->description)
                                        <p class="card-text">{{ $project->description }}</p>
                                    @else
                                        <p class="card-text text-muted fst-italic">{{ __('No description provided') }}</p>
                                    @endif
                                </div>
                                
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center p-2 rounded bg-light">
                                            <i class="bi bi-server text-primary me-2" data-bs-toggle="tooltip" title="{{ __('Repositories') }}"></i>
                                            <span class="fw-medium">{{ $project->repositoriesCount() }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center p-2 rounded bg-light">
                                            <i class="bi bi-journals text-primary me-2" data-bs-toggle="tooltip" title="{{ __('Test Plans') }}"></i>
                                            <span class="fw-medium">{{ $project->testPlansCount() }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center p-2 rounded bg-light">
                                            <i class="bi bi-play-circle text-primary me-2" data-bs-toggle="tooltip" title="{{ __('Test Runs') }}"></i>
                                            <span class="fw-medium">{{ $project->testRunsCount() }}</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center p-2 rounded bg-light">
                                            <i class="bi bi-file-text-fill text-primary me-2" data-bs-toggle="tooltip" title="{{ __('Documents') }}"></i>
                                            <span class="fw-medium">{{ $project->documentsCount() }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Test Case Stats -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted">{{ __('Test Cases') }}</span>
                                        <span class="small fw-medium">{{ $project->casesCount() }}</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        @php
                                            $totalCases = $project->casesCount();
                                            $automatedCases = $project->automatedCasesCount() ?? 0;
                                            $automatedPercent = $totalCases > 0 ? round(($automatedCases / $totalCases) * 100) : 0;
                                            $manualPercent = 100 - $automatedPercent;
                                        @endphp
                                        
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $automatedPercent }}%" 
                                             aria-valuenow="{{ $automatedCases }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="{{ $totalCases }}"
                                             title="{{ $automatedCases }} {{ __('Automated Tests') }} ({{ $automatedPercent }}%)"></div>
                                        <div class="progress-bar bg-secondary" role="progressbar" 
                                             style="width: {{ $manualPercent }}%" 
                                             aria-valuenow="{{ $totalCases - $automatedCases }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="{{ $totalCases }}"
                                             title="{{ $totalCases - $automatedCases }} {{ __('Manual Tests') }} ({{ $manualPercent }}%)"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="small text-muted">{{ __('Automated') }}: {{ $automatedPercent }}%</span>
                                        <span class="small text-muted">{{ __('Manual') }}: {{ $manualPercent }}%</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-footer bg-white border-top py-3">
                                <a href="{{ route('project_show_page', $project->id) }}" class="btn btn-primary w-100">
                                    <i class="bi bi-arrow-right me-1"></i> {{ __('Open Project') }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- No Search Results Message -->
            <div id="noSearchResults" class="text-center py-5 my-4 bg-light rounded d-none">
                <div class="py-4">
                    <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                    <h4 class="mt-3">{{ __('No Matching Projects') }}</h4>
                    <p class="text-muted">{{ __('Try adjusting your search criteria') }}</p>
                    <button id="resetSearch" class="btn btn-outline-secondary mt-2">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> {{ __('Reset Search') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

<!-- Delete Confirmation Modal -->
@can('delete_projects')
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    {{ __('Confirm Deletion') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>{{ __('Are you sure you want to delete the project') }} <strong id="deleteProjectName">Project Name</strong>?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ __('This action cannot be undone. All repositories, test plans, test runs, and documents in this project will be permanently deleted.') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> {{ __('Cancel') }}
                </button>
                <form id="deleteProjectForm" method="POST" action="{{ route('project_delete') }}">
                    @csrf
                    <input type="hidden" name="id" id="deleteProjectId" value="">
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> {{ __('Delete Project') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@section('footer')
    <script src="{{ asset_path('js/project.js') }}"></script>
@endsection
