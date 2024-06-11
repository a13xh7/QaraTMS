@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">
        <div class="page_title border-bottom my-3 d-flex justify-content-between">
            <h3 class="page_title">
                {{__('Dashboard of project')}}: <span class="badge bg-primary text-wrap">{{$project->title}}</span>
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


        <div class="row row-cols-1 row-cols-md-6 g-2 text-secondary ">

            <div class="col">
                <div class="card base_block border shadow-sm rounded">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{route("repository_list_page", $project->id)}}">
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
                <div class="card base_block border shadow-sm rounded">
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
                <div class="card base_block border shadow-sm rounded">
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
                <div class="card base_block border shadow-sm rounded">
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
                <div class="card base_block border shadow-sm rounded">
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
                <div class="card base_block border shadow-sm rounded">
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

        <div class="border-bottom my-3">
            <h3 class="page_title">
                {{ __('Repositories') }}

                @can('add_edit_repositories')
                    <a class="mx-3" href="{{route("repository_create_page", $project->id)}}">
                        <button type="button" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i>
                            {{ __('Add New') }}
                        </button>
                    </a>
                @endcan
            </h3>
        </div>

        <div class="row row-cols-3 g-3">
            @foreach($repositories as $repository)

                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <h3 class="card-title">
                                <a href="{{ route('repository_show_page', [$project->id, $repository->id]) }}">
                                    <i class="bi bi-stack"></i>
                                    {{$repository->title}}</a>
                            </h3>
                            @if($repository->description)
                                <div class="card-text">
                                    {{$repository->description}}
                                </div>
                            @endif
                        </div>

                        <div class="card-footer">
                            <b>{{ $repository->suitesCount() }}</b> {{ __('Test Cases') }}
                            | <b>{{ $repository->casesCount() }}</b> {{ __('Test Suites') }}
                            | <b>{{ $repository->automatedCasesCount() }}</b> {{ __('Automation') }}

                        </div>

                    </div>
                </div>

            @endforeach
        </div>


    </div>

@endsection

