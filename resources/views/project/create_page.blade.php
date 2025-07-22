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
                    <h3 class="page-title mb-1">Create New Project</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="{{ route('project_list_page') }}">Projects</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create New</li>
                        </ol>
                    </nav>
                </div>

                <div>
                    <a href="{{ route('project_list_page') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Projects
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
                                <i class="bi bi-briefcase me-2 text-primary"></i>
                                Project Details
                            </h5>
                        </div>

                        <div class="card-body p-4">
                            <form action="{{ route('project_create') }}" method="POST" id="createProjectForm">
                                @csrf

                                <div class="mb-4">
                                    <label for="title" class="form-label">Project Name <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-briefcase"></i>
                                        </span>
                                        <input type="text"
                                            class="form-control form-control-lg @error('title') is-invalid @enderror"
                                            id="title" name="title" value="{{ old('title') }}" required maxlength="100"
                                            placeholder="Enter a descriptive name for your project">
                                    </div>
                                    <div class="form-text">
                                        Give your project a clear, descriptive name (max 100 characters)
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="4" maxlength="255"
                                        placeholder="Describe the purpose and goals of this project">{{ old('description') }}</textarea>
                                    <div class="form-text">
                                        Provide a brief description of your project's purpose (max 255 characters)
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label d-block">Project Settings</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="create_default_repository"
                                            name="create_default_repository" checked>
                                        <label class="form-check-label" for="create_default_repository">
                                            Create default test repository
                                        </label>
                                        <div class="form-text">
                                            Automatically create a default repository for storing test cases
                                        </div>
                                    </div>

                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="add_team_members"
                                            name="add_team_members">
                                        <label class="form-check-label" for="add_team_members">
                                            Add team members after creation
                                        </label>
                                        <div class="form-text">
                                            You'll be prompted to add team members after creating the project
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-4">
                                    <a href="{{ route('project_list_page') }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-lg me-1"></i> Cancel
                                    </a>

                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-plus-lg me-1"></i> Create Project
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
                                Getting Started
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3 text-muted">What is a Project?</h6>
                            <p class="card-text small">A project is the top-level container for all your testing
                                resources. It helps you organize test repositories, test plans, and test runs for a
                                specific product or initiative.</p>

                            <hr>

                            <h6 class="mb-2">After Creating a Project:</h6>
                            <ul class="small mb-0">
                                <li class="mb-2">Create test repositories to store your test cases</li>
                                <li class="mb-2">Organize test cases into test suites</li>
                                <li class="mb-2">Create test plans to define what to test</li>
                                <li>Execute test runs to track testing progress</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Project Template Card -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-layout-text-window me-2 text-secondary"></i>
                                Project Templates
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="small text-muted mb-3">
                                Start with a template to quickly set up your project with predefined structures:
                            </p>

                            <div class="list-group">
                                <label class="list-group-item d-flex gap-2">
                                    <input class="form-check-input flex-shrink-0" type="radio" name="project_template"
                                        id="template_empty" value="empty" checked>
                                    <span>
                                        <strong>Empty Project</strong>
                                        <small class="d-block text-muted">Start with a blank project</small>
                                    </span>
                                </label>

                                <label class="list-group-item d-flex gap-2">
                                    <input class="form-check-input flex-shrink-0" type="radio" name="project_template"
                                        id="template_agile" value="agile">
                                    <span>
                                        <strong>Agile Testing</strong>
                                        <small class="d-block text-muted">Structure for sprint-based testing</small>
                                    </span>
                                </label>

                                <label class="list-group-item d-flex gap-2">
                                    <input class="form-check-input flex-shrink-0" type="radio" name="project_template"
                                        id="template_web" value="web">
                                    <span>
                                        <strong>Web Application</strong>
                                        <small class="d-block text-muted">UI, API and performance testing</small>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script src="{{ asset_path('js/project.js') }}"></script>
@endsection