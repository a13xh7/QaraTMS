<!-- Still bug-->
@php use App\Models\Project;use App\Models\Repository;
/**
* @var Repository $repository
* @var Project $project
*/
@endphp

@extends('layout.base_layout')

@section('head')
    <link rel="stylesheet" href="{{ asset_path('css/repository.css') }}">
    <link rel="stylesheet" href="{{ asset_path('css/suites_tree.css') }}">
    <link rel="stylesheet" href="{{ asset_path('editor/summernote-repo.css') }}">
    <script src="{{ asset_path('editor/summernote-lite.min.js') }}"></script>
    <script src="{{ asset_path('editor/summernote-lite.min.js') }}"></script>
@endsection

@section('content')
    <div class="d-flex flex-column vh-100 overflow-hidden">
        <!-- Header Bar -->
        <div class="repository-header bg-white border-bottom py-2 px-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="{{ route('project_list_page') }}">{{ __('Projects') }}</a>
                            </li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('project_show_page', $project->id) }}">{{ $project->title }}</a></li>
                            <li class="breadcrumb-item"><a
                                    href="{{ route('repository_list_page', $project->id) }}">{{ __('Repositories') }}</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $repository->title }}</li>
                        </ol>
                    </nav>
                    <h4 class="mb-0 mt-1 d-flex align-items-center">
                        <i class="bi bi-archive-fill text-primary me-2"></i>
                        {{ $repository->title }}
                        <span class="badge bg-light text-dark ms-2 fs-6">{{ $repository->prefix }}</span>
                    </h4>
                </div>

                <div class="d-flex align-items-center">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="viewToggleBtn"
                            title="Toggle View">
                            <i class="bi bi-layout-three-columns"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="refreshBtn" title="Refresh">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="bi bi-plus-lg me-1"></i> {{ __('Create') }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @can('add_edit_test_suites')
                                <li>
                                    <a class="dropdown-item" href="#" onclick="showSuiteForm('create')">
                                        <i class="bi bi-folder-plus me-2"></i> {{ __('Test Suite') }}
                                    </a>
                                </li>
                            @endcan
                            @can('add_edit_test_cases')
                                <li>
                                    <a class="dropdown-item" href="#" onclick="loadTestCaseCreateForm()">
                                        <i class="bi bi-file-earmark-plus me-2"></i> {{ __('Test Case') }}
                                    </a>
                                </li>
                            @endcan
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="bi bi-upload me-2"></i> {{ __('Import Test Cases') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    @can('add_edit_repositories')
                        <a href="{{ route('repository_edit_page', [$project->id, $repository->id]) }}"
                            class="btn btn-outline-secondary btn-sm ms-2" title="{{ __('Repository Settings') }}">
                            <i class="bi bi-gear me-1"></i> {{ __('Settings') }}
                        </a>
                    @endcan
                </div>
            </div>

            <!-- Repository Stats -->
            <div class="d-flex mt-2 small">
                <div class="me-3 d-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Test Suites') }}">
                    <i class="bi bi-folder2 text-muted me-1"></i>
                    <span class="fw-medium">{{ $repository->suitesCount() }}</span>
                </div>
                <div class="me-3 d-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Test Cases') }}">
                    <i class="bi bi-file-earmark-text text-muted me-1"></i>
                    <span class="fw-medium">{{ $repository->casesCount() }}</span>
                </div>
                <div class="me-3 d-flex align-items-center" data-bs-toggle="tooltip" title="{{ __('Automated Tests') }}">
                    <i class="bi bi-robot text-muted me-1"></i>
                    <span class="fw-medium">{{ $repository->automatedCasesCount() }}</span>
                </div>

                @if($repository->description)
                    <div class="ms-2 text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ $repository->description }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="d-flex flex-grow-1 overflow-hidden">
            <!-- Test Suites Tree Column -->
            <div class="suites-tree-column bg-light border-end" id="suites_tree_col">
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">{{ __('Test Suites') }}</h5>

                        @can('add_edit_test_suites')
                            <button id="add_root_suite_btn" class="btn btn-sm btn-outline-primary" type="button"
                                title="{{ __('Add Root Test Suite') }}" onclick="showSuiteForm('create')">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        @endcan
                    </div>

                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="suiteSearch"
                            placeholder="{{ __('Search suites...') }}">
                    </div>

                    <div class="tree-actions mb-3">
                        <div class="btn-group btn-group-sm w-100">
                            <button type="button" class="btn btn-outline-secondary" id="expandAllBtn">
                                <i class="bi bi-arrows-expand"></i> {{ __('Expand All') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="collapseAllBtn">
                                <i class="bi bi-arrows-collapse"></i> {{ __('Collapse All') }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="suites-tree-container overflow-auto px-2">
                    <ul id="tree" class="tree-root">
                        <li class="tree-empty-placeholder text-center py-5 text-muted">
                            <i class="bi bi-folder2 fs-1"></i>
                            <p class="mt-2">{{ __('No test suites found') }}</p>
                            @can('add_edit_test_suites')
                                <button class="btn btn-sm btn-outline-primary" onclick="showSuiteForm('create')">
                                    <i class="bi bi-plus-lg me-1"></i> {{ __('Create Test Suite') }}
                                </button>
                            @endcan
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Test Cases List Column -->
            <div id="test_cases_list_col" class="test-cases-column flex-grow-1 d-flex flex-column">
                <div class="test-cases-header border-bottom bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <span class="text-muted">{{ __('Suite') }}:</span>
                                <span id="test_cases_list_site_title">{{ __('Select Test Suite') }}</span>
                            </h5>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="input-group input-group-sm me-2" style="width: 200px;">
                                <input type="text" class="form-control" id="testCaseSearch"
                                    placeholder="{{ __('Search test cases...') }}">
                                <button class="btn btn-outline-secondary" type="button" id="clearTestCaseSearch">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>

                            <div class="btn-group btn-group-sm me-2">
                                <button type="button" class="btn btn-outline-secondary active" id="viewAllBtn">
                                    {{ __('All') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="viewAutomatedBtn">
                                    {{ __('Automated') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="viewManualBtn">
                                    {{ __('Manual') }}
                                </button>
                            </div>

                            @can('add_edit_test_cases')
                                <button class="btn btn-primary btn-sm" type="button" title="{{ __('Add Test Case') }}"
                                    onclick="loadTestCaseCreateForm()">
                                    <i class="bi bi-plus-lg me-1"></i> {{ __('Test Case') }}
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>

                <div id="test_cases_list" class="test-cases-container overflow-auto p-3">
                    <!-- Empty state for test cases -->
                    <div class="test-cases-empty-state text-center py-5">
                        <i class="bi bi-file-earmark-text text-muted fs-1"></i>
                        <h5 class="mt-3">{{ __('No Test Cases Available') }}</h5>
                        <p class="text-muted">{{ __('Select a test suite from the left panel or create a new test case') }}
                        </p>
                        @can('add_edit_test_cases')
                            <button class="btn btn-primary" onclick="loadTestCaseCreateForm()">
                                <i class="bi bi-plus-lg me-1"></i> {{ __('Create Test Case') }}
                            </button>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Test Case Detail Column -->
            <div id="test_case_col" class="test-case-column border-start bg-white d-none">
                <div id="test_case_area" class="h-100 overflow-auto">
                    <!-- Test case content will be loaded here -->
                    <div class="test-case-empty-state text-center py-5">
                        <i class="bi bi-file-earmark-text text-muted fs-1"></i>
                        <h5 class="mt-3">{{ __('No Test Case Selected') }}</h5>
                        <p class="text-muted">{{ __('Select a test case from the list to view details') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Suite Form Modal -->
    <div id="test_suite_form_overlay" class="overlay" style="display: none">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tsf_title">{{ __('Create Test Suite') }}</h5>
                    <button type="button" class="btn-close" onclick="closeSuiteForm()"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <input id="repository_id" type="hidden" value="{{ $repository->id }}">
                        <input id="parent_suite_id" type="hidden" value="">
                        <input id="edit_suite_id" type="hidden" value="">

                        <div class="mb-3">
                            <label for="test_suite_title_input" class="form-label">{{ __('Suite Name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="test_suite_title_input"
                                placeholder="{{ __('Enter test suite name') }}" required>
                            <div class="invalid-feedback">{{ __('Suite name is required') }}</div>
                        </div>

                        <div class="mb-3">
                            <label for="test_suite_description" class="form-label">{{ __('Description') }}</label>
                            <textarea class="form-control" id="test_suite_description" rows="3"
                                placeholder="{{ __('Optional description') }}"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="closeSuiteForm()">
                        {{ __('Cancel') }}
                    </button>
                    <button id="tsf_update_btn" type="button" class="btn btn-primary" style="display: none"
                        onclick="updateSuite()">
                        <i class="bi bi-save me-1"></i> {{ __('Update') }}
                    </button>
                    <button id="tsf_create_btn" type="button" class="btn btn-primary" onclick="createSuite()">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('Create') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="bi bi-upload me-2"></i>
                        {{ __('Import Test Cases') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('Select a file to import test cases into your repository.') }}</p>
                    <div class="mb-3">
                        <label for="importFile" class="form-label">{{ __('Import File') }}</label>
                        <input class="form-control" type="file" id="importFile" accept=".csv,.xlsx,.json">
                        <div class="form-text">
                            {{ __('Supported formats: CSV, Excel, JSON') }}
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="targetSuite" class="form-label">{{ __('Target Test Suite') }}</label>
                        <select class="form-select" id="targetSuite">
                            <option value="">{{ __('-- Root Level --') }}</option>
                            <!-- Test suites will be populated via JavaScript -->
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="createMissingSuites" checked>
                        <label class="form-check-label" for="createMissingSuites">
                            {{ __('Create missing test suites automatically') }}
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> {{ __('Import') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script>
        let repository_id = {{ $repository->id }};
        let canEditSuites = {{ $canEditSuites }};
        let canDeleteSuites = {{ $canDeleteSuites }};
    </script>
    <script src="{{ asset_path('js/repo/repository.js') }}"></script>
@endsection