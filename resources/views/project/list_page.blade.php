@extends('layout.base_layout')
@section('content')
    @include('layout.sidebar_nav')
    <div class="col">
        <div class="border-bottom my-3">
            <h3 class="page_title">
                {{ __('Projects') }}
                @can('add_edit_projects')
                    <a href="{{route("project_create_page")}}">
                        <button type="button" class="btn btn-primary"><i class="bi bi-plus-lg"></i>
                            {{ __('Create new project') }}
                        </button>
                    </a>
                @endcan
            </h3>
        </div>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @foreach($projects as $project)
                <div class="col">
                    <div class="card border-primary h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <a class=""
                                   href="{{route("project_show_page", $project->id)}}">{{$project->title}}</a>
                            </h5>
                            <p class="card-text">{{$project->description}}</p>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <a href="{{route("repository_list_page", $project->id)}}">
                                    <i class="bi bi-server"></i>
                                    {{ __('Repositories') }}
                                </a>
                                <span class="badge bg-secondary">
                                    {{$project->repositoriesCount()}}
                                </span>
                            </li>
                            <li class="list-group-item">
                                <a href="{{route("test_plan_list_page", $project->id)}}">
                                    <i class="bi bi-journals"></i> {{ __('Test Plans') }}
                                </a>
                                <span class="badge bg-secondary">
                                    {{$project->testPlansCount()}}
                                </span>
                            </li>
                            <li class="list-group-item">
                                <a href="{{route("test_run_list_page", $project->id)}}">
                                    <i class="bi bi-play-circle"></i> {{ __('Test Runs') }}
                                </a>
                                <span class="badge bg-secondary">{{$project->testRunsCount()}}</span>
                            </li>
                            <li class="list-group-item">
                                <a href="{{route("project_documents_list_page", $project->id)}}">
                                    <i class="bi bi-file-text-fill"></i> {{ __('Documents') }}
                                    <span class="badge bg-secondary">{{$project->documentsCount()}}</span>
                                </a>
                            </li>
                            <li class="list-group-item">
                                {{ __('Test Suites') }}: <span
                                        class="badge bg-secondary">{{$project->suitesCount()}}</span>
                            </li>
                            <li class="list-group-item">
                                {{ __('Test Cases') }}: <span
                                        class="badge bg-secondary">{{$project->casesCount()}}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
