@extends('layout.base_layout')

@section('head')
    <link rel="stylesheet" href="{{ asset_path('css/project.css') }}">
@endsection

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">
        <div class="page_title border-bottom my-3 d-flex justify-content-between align-items-center">
            <h3 class="page_title mb-0">
                {{__('Dashboard of project')}}: <span class="badge bg-primary px-2 text-wrap">{{$project->title}}</span>
            </h3>
            <div>
                @can('add_edit_projects')
                    <a href="{{route('project_edit_page', $project->id)}}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-gear"></i>
                        {{__('Settings')}}
                    </a>
                @endcan
            </div>
        </div>

        <!-- Project Stats Cards -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-6 g-3 mb-4 text-secondary">
            <div class="col">
                <div class="card h-100 base_block border shadow-sm rounded hover-shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{route("repository_list_page", $project->id)}}" class="text-decoration-none">
                                <i class="bi bi-server"></i>
                                {{ __('Repositories') }}
                            </a>
                            <span class="badge bg-secondary">
                                {{$project->repositoriesCount()}}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 base_block border shadow-sm rounded hover-shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-stack"></i> {{ __('Test Suites') }}</span>
                            <span class="badge bg-secondary">
                                {{ $project->suitesCount() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 base_block border shadow-sm rounded hover-shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-file-earmark-text"></i> {{ __('Test Cases') }}</span>
                            <span class="badge bg-secondary">
                                {{ $project->casesCount() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 base_block border shadow-sm rounded hover-shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-robot"></i> {{ __('Automation') }}</span>
                            <span class="badge bg-secondary">
                                {{ $project->getAutomationPercent() }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 base_block border shadow-sm rounded hover-shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{route("test_plan_list_page", $project->id)}}">
                                <i class="bi bi-journals"></i> {{ __('Test Plans') }}
                            </a>
                            <span class="badge bg-secondary">
                                {{$project->testPlansCount()}}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100 base_block border shadow-sm rounded hover-shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{route("test_run_list_page", $project->id)}}">
                                <i class="bi bi-play-circle"></i> {{ __('Test Runs') }}
                            </a>
                            <span class="badge bg-secondary">{{$project->testRunsCount()}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-bottom my-3 d-flex justify-content-between align-items-center">
            <h3 class="page_title mb-0">
                {{ __('Repositories') }}
            </h3>
            <div>
                @can('add_edit_repositories')
                    <a href="{{route("repository_create_page", $project->id)}}">
                        <button type="button" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i>
                            {{ __('Add New') }}
                        </button>
                    </a>
                @endcan

                <!-- Add search/filter input -->
                <div class="input-group input-group-sm d-inline-flex w-auto ms-2">
                    <input type="text" class="form-control" id="repositorySearch"
                        placeholder="{{ __('Search repositories') }}">
                    <button class="btn btn-outline-secondary" type="button"><i class="bi bi-search"></i></button>
                </div>
            </div>
        </div>

        <!-- Repository Cards -->
        @if(count($repositories) > 0)
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3">
                @foreach($repositories as $repository)
                    <div class="col repository-card">
                        <div class="card h-100 base_block border shadow-sm rounded hover-shadow">
                            <div class="card-body">
                                <h3 class="card-title h5">
                                    <a href="{{ route('repository_show_page', [$project->id, $repository->id]) }}"
                                        class="text-decoration-none">
                                        <i class="bi bi-stack"></i>
                                        {{$repository->title}}
                                    </a>
                                </h3>
                                @if($repository->description)
                                    <div class="card-text text-muted">
                                        {{Str::limit($repository->description, 100)}}
                                    </div>
                                @endif

                                <!-- Add progress bar for automation -->
                                @php
                                    $automationPercent = $repository->casesCount() > 0 ? round(($repository->automatedCasesCount() / $repository->casesCount() * 100), 1) : 0;
                                    $progressClass = $automationPercent > 75 ? 'bg-success' : ($automationPercent > 50 ? 'bg-info' : ($automationPercent > 25 ? 'bg-warning' : 'bg-danger'));
                                @endphp
                                <div class="mt-3">
                                    <label class="form-label d-flex justify-content-between mb-1">
                                        <small>{{ __('Automation Coverage') }}</small>
                                        <small>{{$automationPercent}}%</small>
                                    </label>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{$progressClass}}" role="progressbar"
                                            style="width: {{$automationPercent}}%" aria-valuenow="{{$automationPercent}}"
                                            aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-transparent">
                                <div class="d-flex flex-wrap gap-2 text-muted small">
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Test Suites') }}">
                                        <i class="bi bi-folder2"></i> {{ $repository->suitesCount() }}
                                    </span>
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Test Cases') }}">
                                        <i class="bi bi-file-earmark-text"></i> {{ $repository->casesCount() }}
                                    </span>
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Automated Cases') }}">
                                        <i class="bi bi-robot"></i> {{ $repository->automatedCasesCount() }}
                                    </span>
                                    @if($repository->updated_at)
                                        <span class="ms-auto" data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ __('Last Updated') }}">
                                            <i class="bi bi-clock-history"></i> {{ $repository->updated_at->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                {{ __('No repositories found. Create your first repository to get started.') }}
            </div>
        @endif
    </div>
@endsection

@section('footer')
    <script src="{{ asset_path('js/project.js') }}"></script>
@endsection