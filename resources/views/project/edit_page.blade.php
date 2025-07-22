@extends('layout.base_layout')

@section('head')
    <link rel="stylesheet" href="{{ asset_path('css/project.css') }}">
@endsection

@section('content')
    @include('layout.sidebar_nav')

    <div class="flex-grow-1 main-content">
        <div class="container-fluid px-4 py-4">
            <!-- Breadcrumb and Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="page-title mb-1">Edit Project</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="{{ route('project_list_page') }}">Projects</a></li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('project_show_page', $project->id) }}">{{ $project->title }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                        </ol>
                    </nav>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('project_show_page', $project->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Project
                    </a>

                    @can('delete_projects')
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteConfirmModal">
                            <i class="bi bi-trash3 me-1"></i> Delete
                        </button>
                    @endcan
                </div>
            </div>

            <!-- Alert Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span>Form Validation Error</span>
                    </h5>
                    <hr>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <!-- Main Form Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-briefcase me-2 text-primary"></i>
                                Project Details
                            </h5>
                        </div>

                        <div class="card-body p-4">
                            <form action="{{ route('project_update') }}" method="POST" id="editProjectForm">
                                @csrf
                                <input type="hidden" name="id" value="{{ $project->id }}">

                                <div class="mb-4">
                                    <label for="title" class="form-label">Project Name <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-briefcase"></i>
                                        </span>
                                        <input type="text"
                                            class="form-control form-control-lg @error('title') is-invalid @enderror"
                                            id="title" name="title" value="{{ old('title', $project->title) }}" required
                                            maxlength="100" placeholder="Enter a descriptive name for your project">
                                    </div>
                                    <div class="form-text">
                                        Give your project a clear, descriptive name (max 100 characters)
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="4" maxlength="255"
                                        placeholder="Describe the purpose and goals of this project">{{ old('description', $project->description) }}</textarea>
                                    <div class="form-text">
                                        Provide a brief description of your project's purpose (max 255 characters)
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-4">
                                    <a href="{{ route('project_show_page', $project->id) }}"
                                        class="btn btn-outline-secondary">
                                        <i class="bi bi-x-lg me-1"></i> Cancel
                                    </a>

                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-save me-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Project Info Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2 text-info"></i>
                                Project Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Created</span>
                                    <span>{{ $project->created_at->format('M d, Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Last Updated</span>
                                    <span>{{ $project->updated_at->format('M d, Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Repositories</span>
                                    <span class="badge bg-primary rounded-pill">{{ $project->repositoriesCount() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Test Plans</span>
                                    <span class="badge bg-primary rounded-pill">{{ $project->testPlansCount() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Test Runs</span>
                                    <span class="badge bg-primary rounded-pill">{{ $project->testRunsCount() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Test Cases</span>
                                    <span class="badge bg-primary rounded-pill">{{ $project->casesCount() }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-lightning-charge me-2 text-warning"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <a href="{{ route('repository_list_page', $project->id) }}"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-server me-2 text-primary"></i>
                                        Manage Repositories
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </a>
                                <a href="{{ route('test_plan_list_page', $project->id) }}"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-journals me-2 text-primary"></i>
                                        Manage Test Plans
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </a>
                                <a href="{{ route('test_run_list_page', $project->id) }}"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-play-circle me-2 text-primary"></i>
                                        Manage Test Runs
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </a>
                                <a href="{{ route('project_documents_list_page', $project->id) }}"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="bi bi-file-text-fill me-2 text-primary"></i>
                                        Manage Documents
                                    </div>
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @can('delete_projects')
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteConfirmModalLabel">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Confirm Deletion
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the project <strong>"{{ $project->title }}"</strong>?</p>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            This action cannot be undone. All repositories, test plans, test runs, and documents in this project
                            will be permanently deleted.
                        </div>

                        <div class="mt-3">
                            <p class="mb-1"><strong>This will delete:</strong></p>
                            <ul>
                                <li>{{ $project->repositoriesCount() }} repositories</li>
                                <li>{{ $project->testPlansCount() }} test plans</li>
                                <li>{{ $project->testRunsCount() }} test runs</li>
                                <li>{{ $project->casesCount() }} test cases</li>
                                <li>{{ $project->documentsCount() }} documents</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>
                        <form method="POST" action="{{ route('project_delete') }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $project->id }}">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash3 me-1"></i> Delete Project
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