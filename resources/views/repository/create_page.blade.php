@php use App\Models\Project;use Illuminate\Support\MessageBag;
/**
* @var Project $project
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="page-title mb-1">Create Test Repository</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="{{ route('project_list_page') }}">Projects</a></li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('project_show_page', $project->id) }}">{{ $project->title }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('repository_list_page', $project->id) }}">Test
                                    Repositories</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create New</li>
                        </ol>
                    </nav>
                </div>

                <div>
                    <a href="{{ route('repository_list_page', $project->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Repositories
                    </a>
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
                            <form method="POST" action="{{ route('repository_create') }}" id="createRepositoryForm">
                                @csrf
                                <input type="hidden" name="project_id" value="{{ $project->id }}">

                                <div class="mb-4">
                                    <label for="title" class="form-label">Repository Name <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-folder"></i>
                                        </span>
                                        <input name="title" type="text"
                                            class="form-control form-control-lg @error('title') is-invalid @enderror"
                                            id="title" value="{{ old('title') }}" required maxlength="100"
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
                                            name="prefix" id="prefix" value="{{ old('prefix') }}" required maxlength="3"
                                            pattern="[^\s]+" style="text-transform:uppercase" placeholder="e.g. TST">
                                    </div>
                                    <div class="form-text">
                                        A short prefix for test case IDs (max 3 characters, no spaces). Example: TST-123
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea name="description" id="description"
                                        class="form-control @error('description') is-invalid @enderror" rows="4"
                                        maxlength="255"
                                        placeholder="Describe the purpose of this repository">{{ old('description') }}</textarea>
                                    <div class="form-text">
                                        Provide a brief description of this repository's purpose (max 255 characters)
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Repository Structure</label>
                                    <div class="card bg-light border">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="structure_type"
                                                    id="structureEmpty" value="empty" checked>
                                                <label class="form-check-label" for="structureEmpty">
                                                    <strong>Empty Repository</strong>
                                                    <p class="text-muted small mb-0">Start with an empty repository and
                                                        create your own structure</p>
                                                </label>
                                            </div>

                                            <hr>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="structure_type"
                                                    id="structureTemplate" value="template">
                                                <label class="form-check-label" for="structureTemplate">
                                                    <strong>Use Template</strong>
                                                    <p class="text-muted small mb-0">Start with a predefined structure
                                                        based on common testing practices</p>
                                                </label>
                                            </div>

                                            <div class="ms-4 mt-2 template-options d-none">
                                                <select class="form-select" name="template_id" id="templateSelect" disabled>
                                                    <option value="1">Basic Test Structure</option>
                                                    <option value="2">Agile Testing Template</option>
                                                    <option value="3">Web Application Testing</option>
                                                    <option value="4">API Testing</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-4">
                                    <a href="{{ route('repository_list_page', $project->id) }}"
                                        class="btn btn-outline-secondary">
                                        <i class="bi bi-x-lg me-1"></i> Cancel
                                    </a>

                                    <button type="submit" class="btn btn-primary px-4"
                                        onclick="event.preventDefault(); document.getElementById('createRepositoryForm').submit();">
                                        <i class="bi bi-save me-1"></i> Create Repository
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Help Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2 text-info"></i>
                                About Test Repositories
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3 text-muted">What is a Test Repository?</h6>
                            <p class="card-text small">A test repository is a container for organizing your test cases,
                                test suites, and other testing artifacts. It helps you structure and manage your testing
                                resources efficiently.</p>

                            <hr>

                            <h6 class="mb-2">Key Features:</h6>
                            <ul class="small mb-0">
                                <li class="mb-2"><strong>Organization:</strong> Group related test cases into suites and
                                    folders</li>
                                <li class="mb-2"><strong>Identification:</strong> Use prefixes to create unique IDs for
                                    test cases</li>
                                <li class="mb-2"><strong>Reusability:</strong> Create test cases once and reuse them in
                                    multiple test plans</li>
                                <li><strong>Versioning:</strong> Track changes to test cases over time</li>
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
                                    <span id="prefixDisplay">PRE</span>-<span class="text-secondary">123</span>
                                </h3>
                            </div>

                            <div class="mt-3 small">
                                <p class="mb-1"><strong>Examples:</strong></p>
                                <ul class="mb-0">
                                    <li><strong>API</strong>-101: API Authentication Test</li>
                                    <li><strong>UI</strong>-202: User Interface Navigation Test</li>
                                    <li><strong>SEC</strong>-303: Security Validation Test</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @can('delete_repositories')
        <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
            aria-hidden="true">
        </div>
    @endcan

    @section('footer')
        <script src="{{ asset_path('js/repo/repository.js') }}"></script>
    @endsection
@endsection