@php use App\Models\Repository;use Illuminate\Support\MessageBag;
/**
* @var Repository $repository
* @var MessageBag $errors
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
                    <h3 class="page-title mb-1">Edit Repository</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="{{ route('project_list_page') }}">Projects</a></li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('project_show_page', $repository->project_id) }}">{{ $project->name ?? 'Project' }}</a>
                            </li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('repository_list_page', $repository->project_id) }}">Repositories</a>
                            </li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('repository_show_page', [$repository->project_id, $repository->id]) }}">{{ $repository->title }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                        </ol>
                    </nav>
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('repository_list_page', $repository->project_id) }}"
                        class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Repository
                    </a>

                    @can('delete_repositories')
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
                                <i class="bi bi-archive me-2 text-primary"></i>
                                Repository Details
                            </h5>
                        </div>

                        <div class="card-body p-4">
                            <form method="POST" action="{{ route('repository_update') }}" id="editRepositoryForm">
                                @csrf
                                <input type="hidden" name="id" value="{{ $repository->id }}">
                                <input type="hidden" name="project_id" value="{{ $repository->project_id }}">

                                <div class="mb-4">
                                    <label for="title" class="form-label">Repository Name <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-folder"></i>
                                        </span>
                                        <input type="text"
                                            class="form-control form-control-lg @error('title') is-invalid @enderror"
                                            id="title" name="title" required maxlength="100"
                                            value="{{ old('title', $repository->title) }}"
                                            placeholder="Enter a descriptive name for this repository">
                                    </div>
                                    <div class="form-text">
                                        Give your repository a clear, descriptive name (max 100 characters)
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="prefix" class="form-label">Prefix <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-hash"></i>
                                        </span>
                                        <input type="text"
                                            class="form-control form-control-lg @error('prefix') is-invalid @enderror"
                                            id="prefix" name="prefix" required maxlength="3" pattern="[^\s]+"
                                            title="Please don't use whitespace" style="text-transform:uppercase"
                                            value="{{ old('prefix', $repository->prefix) }}" placeholder="e.g. TST">
                                    </div>
                                    <div class="form-text">
                                        A short prefix for test case IDs (max 3 characters, no spaces). Example: TST-123
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="4" maxlength="255"
                                        placeholder="Describe the purpose of this repository">{{ old('description', $repository->description) }}</textarea>
                                    <div class="form-text">
                                        Provide a brief description of this repository's purpose (max 255 characters)
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-4">
                                    <a href="{{ route('repository_show_page', [$repository->project_id, $repository->id]) }}"
                                        class="btn btn-outline-secondary">
                                        <i class="bi bi-x-lg me-1"></i> Cancel
                                    </a>

                                    <button type="submit" class="btn btn-primary px-4"
                                        onclick="event.preventDefault(); document.getElementById('editRepositoryForm').submit();">
                                        <i class="bi bi-save me-1"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Repository Info Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2 text-info"></i>
                                Repository Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Created</span>
                                    <span>{{ $repository->created_at->format('M d, Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Last Updated</span>
                                    <span>{{ $repository->updated_at->format('M d, Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Test Suites</span>
                                    <span class="badge bg-primary rounded-pill">{{ $repository->suitesCount() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Test Cases</span>
                                    <span class="badge bg-primary rounded-pill">{{ $repository->casesCount() }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted">Automated Tests</span>
                                    <span
                                        class="badge bg-success rounded-pill">{{ $repository->automatedCasesCount() }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Prefix Preview Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-eye me-2 text-secondary"></i>
                                Prefix Preview
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                The prefix will be used to create unique identifiers for your test cases. Here's how it
                                will look:
                            </p>

                            <div class="d-flex align-items-center justify-content-center p-3 bg-light rounded">
                                <h3 class="mb-0 text-primary" id="prefixPreview">
                                    <span id="prefixDisplay">{{ $repository->prefix }}</span>-<span
                                        class="text-secondary">123</span>
                                </h3>
                            </div>

                            <div class="alert alert-warning mt-3 small">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Warning:</strong> Changing the prefix will not update existing test case IDs.
                                This change only affects new test cases.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    @can('delete_repositories')
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
                        <p>Are you sure you want to delete the repository <strong>"{{ $repository->title }}"</strong>?</p>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            This action cannot be undone. All test suites and test cases in this repository will be permanently
                            deleted.
                        </div>

                        <div class="mt-3">
                            <p class="mb-1"><strong>This will delete:</strong></p>
                            <ul>
                                <li>{{ $repository->suitesCount() }} test suites</li>
                                <li>{{ $repository->casesCount() }} test cases</li>
                                <li>All associated test results</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </button>
                        <form method="POST" action="{{ route('repository_delete') }}" id="deleteRepositoryForm">
                            @csrf
                            <input type="hidden" name="id" value="{{ $repository->id }}" id="repository_id">
                            <input type="hidden" name="project_id" value="{{ $repository->project_id }}">
                            <button type="submit" class="btn btn-danger"
                                onclick="event.preventDefault(); document.getElementById('deleteRepositoryForm').submit();">
                                <i class="bi bi-trash3 me-1"></i> Delete Repository
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @section('footer')
        <script src="{{ asset_path('js/repo/repository.js') }}"></script>
    @endsection
@endsection