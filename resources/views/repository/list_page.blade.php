@php use App\Models\Project;use App\Models\Repository;
/**
 * @var Repository[] $repositories
 * @var Project $project
 */
@endphp
@extends('layout.base_layout')

@section('head')
    <link rel="stylesheet" href="{{ asset_path('css/repository.css') }}">
@endsection

@section('content')
        @include('layout.sidebar_nav')

        <div class="flex-grow-1 main-content">
            <div class="container-fluid px-4 py-4">
                <!-- Breadcrumb and Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="page-title mb-1">Test Repositories</h3>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 small">
                                <li class="breadcrumb-item"><a href="{{ route('project_list_page') }}">Projects</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('project_show_page', $project->id) }}">{{ $project->title }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Repositories</li>
                            </ol>
                        </nav>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="input-group me-3">
                            <input type="text" class="form-control" id="repositorySearch" placeholder="Search repositories..." aria-label="Search repositories">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>

                        @can('add_edit_repositories')
                            <a href="{{ route('repository_create_page', $project->id) }}" class="btn btn-primary px-3 text-nowrap">
                                <i class="bi bi-plus-lg me-1"></i> New Repo
                            </a>
                        @endcan
                    </div>
                </div>

                <!-- Repository Stats -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <div class="row g-0 text-center">
                            <div class="col-md-3 border-end">
                                <h3 class="mb-0 fw-bold text-primary">{{ count($repositories) }}</h3>
                                <p class="text-muted mb-0 small">Repositories</p>
                            </div>
                            <div class="col-md-3 border-end">
                                <h3 class="mb-0 fw-bold text-primary">{{ $repositories->sum(function ($repo) {
        return $repo->suitesCount(); }) }}</h3>
                                <p class="text-muted mb-0 small">Test Suites</p>
                            </div>
                            <div class="col-md-3 border-end">
                                <h3 class="mb-0 fw-bold text-primary">{{ $repositories->sum(function ($repo) {
        return $repo->casesCount(); }) }}</h3>
                                <p class="text-muted mb-0 small">Test Cases</p>
                            </div>
                            <div class="col-md-3">
                                <h3 class="mb-0 fw-bold text-primary">{{ $repositories->sum(function ($repo) {
        return $repo->automatedCasesCount(); }) }}</h3>
                                <p class="text-muted mb-0 small">Automated Tests</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                @if(count($repositories) == 0)
                    <div class="text-center py-5 my-4 bg-light rounded">
                        <div class="py-4">
                            <i class="bi bi-archive text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">No Test Repositories Found</h4>
                            <p class="text-muted">Create your first test repository to start organizing your test cases.</p>

                            @can('add_edit_repositories')
                                <a href="{{ route('repository_create_page', $project->id) }}" class="btn btn-primary mt-2">
                                    <i class="bi bi-plus-lg me-1"></i> Create Repository
                                </a>
                            @endcan
                        </div>
                    </div>
                @endif

                <!-- Repository Grid -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4" id="repositoryGrid">
                    @php
                    // Calculate automated test percentages for all repositories
                    $repositoryStats = [];
                    foreach ($repositories as $repo) {
                        $totalCases = $repo->casesCount() ?? 0;
                        $automatedCases = $repo->automatedCasesCount() ?? 0;
                        $automatedPercent = $totalCases > 0 ? round(($automatedCases / $totalCases) * 100) : 0;
                        $repositoryStats[$repo->id] = [
                            'totalCases' => $totalCases,
                            'automatedCases' => $automatedCases,
                            'automatedPercent' => $automatedPercent
                        ];
                    }
                    @endphp
                    @foreach($repositories as $repository)
                        <div class="col repository-item">
                            <div class="card h-100 border-0 shadow-sm repository-card">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="repository-icon me-2">
                                            <i class="bi bi-archive-fill text-primary"></i>
                                        </div>
                                        <h5 class="mb-0 repository-title">{{ $repository->title }}</h5>
                                    </div>

                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('repository_show_page', [$project->id, $repository->id]) }}">
                                                    <i class="bi bi-eye me-2"></i> View Repository
                                                </a>
                                            </li>
                                            @can('add_edit_repositories')
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('repository_edit_page', [$project->id, $repository->id]) }}">
                                                        <i class="bi bi-pencil me-2"></i> Edit Repository
                                                    </a>
                                                </li>
                                            @endcan
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('repository_show_page', [$project->id, $repository->id]) }}">
                                                    <i class="bi bi-plus-circle me-2"></i> Manage Test Cases
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                                                    <i class="bi bi-upload me-2"></i> Import Test Cases
                                                </a>
                                            </li>
                                            @can('delete_repositories')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" 
                                                       data-bs-toggle="modal" 
                                                       data-bs-target="#deleteModal" 
                                                       data-repository-id="{{ $repository->id }}"
                                                       data-repository-title="{{ $repository->title }}">
                                                        <i class="bi bi-trash me-2"></i> Delete Repository
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </div>

                                <div class="card-body d-flex flex-column">
                                    <div class="mb-3">
                                        <span class="badge bg-light text-dark me-1">
                                            <i class="bi bi-hash me-1"></i>{{ $repository->prefix }}
                                        </span>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-calendar-event me-1"></i>{{ $repository->created_at->format('M d, Y') }}
                                        </span>
                                    </div>

                                    <div class="repository-description flex-grow-1">
                                        @if($repository->description)
                                            <p class="card-text text-muted mb-0">{{ $repository->description }}</p>
                                        @else
                                            <p class="card-text text-muted fst-italic mb-0">No description provided</p>
                                        @endif
                                    </div>

                                    <div class="progress-container mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="small text-muted">Automated Tests</span>
                                            <span class="small fw-medium">{{ $repositoryStats[$repository->id]['automatedPercent'] }}%</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $repositoryStats[$repository->id]['automatedPercent'] }}%;" 
                                                 aria-valuenow="{{ $repositoryStats[$repository->id]['automatedPercent'] }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"
                                                 title="{{ $repositoryStats[$repository->id]['automatedCases'] }} of {{ $repositoryStats[$repository->id]['totalCases'] }} tests automated"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer bg-white border-top d-flex justify-content-between align-items-center py-3">
                                    <div class="d-flex gap-3">
                                        <div class="d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Test Suites">
                                            <i class="bi bi-folder2 text-muted me-1"></i>
                                            <span class="fw-medium">{{ $repository->suitesCount() }}</span>
                                        </div>
                                        <div class="d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Test Cases">
                                            <i class="bi bi-file-earmark-text text-muted me-1"></i>
                                            <span class="fw-medium">{{ $repository->casesCount() }}</span>
                                        </div>
                                        <div class="d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Automated Tests">
                                            <i class="bi bi-robot text-muted me-1"></i>
                                            <span class="fw-medium">{{ $repository->automatedCasesCount() }}</span>
                                        </div>
                                    </div>

                                    <a href="{{ route('repository_show_page', [$project->id, $repository->id]) }}" class="btn btn-sm btn-primary">
                                        Open <i class="bi bi-arrow-right ms-1"></i>
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
                        <h4 class="mt-3">No Matching Repositories</h4>
                        <p class="text-muted">Try adjusting your search criteria</p>
                        <button id="resetSearch" class="btn btn-outline-secondary mt-2">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <!-- Delete Confirmation Modal -->
    @can('delete_repositories')
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the repository <strong id="deleteRepositoryName">Repository Name</strong>?</p>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            This action cannot be undone. All test suites and test cases in this repository will be permanently deleted.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>
                        <form id="deleteRepositoryForm" method="POST" action="{{ route('repository_delete') }}">
                            @csrf
                            <input type="hidden" name="id" id="deleteRepositoryId" value="{{ $repository->id }}">
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <button type="submit" class="btn btn-danger"
                                onclick="event.preventDefault(); document.getElementById('deleteRepositoryForm').submit();">
                                <i class="bi bi-trash me-1"></i> Delete Repository
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="bi bi-upload me-2"></i>
                        Import Test Cases
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Select a file to import test cases into your repository.</p>
                    <div class="mb-3">
                        <label for="importRepository" class="form-label">Target Repository</label>
                        <select class="form-select" id="importRepository" required>
                            @foreach($repositories as $repository)
                                <option value="{{ $repository->id }}">{{ $repository->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="importFile" class="form-label">Import File</label>
                        <input class="form-control" type="file" id="importFile" accept=".csv,.xlsx,.json">
                        <div class="form-text">
                            Supported formats: CSV, Excel, JSON
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="createMissingSuites" checked>
                        <label class="form-check-label" for="createMissingSuites">
                            Create missing test suites automatically
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                </div>
            </div>
        </div>
    </div>

    @section('footer')
        <script src="{{ asset_path('js/repo/repository.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const deleteModal = document.getElementById('deleteModal');
                if (deleteModal) {
                    deleteModal.addEventListener('show.bs.modal', function(event) {
                        const button = event.relatedTarget;
                        const repositoryId = button.getAttribute('data-repository-id');
                        const repositoryTitle = button.getAttribute('data-repository-title');
                        
                        document.getElementById('deleteRepositoryId').value = repositoryId;
                        document.getElementById('deleteRepositoryName').textContent = repositoryTitle;
                    });
                }
            });
        </script>
    @endsection
@endsection


