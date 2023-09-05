@extends('layout.base_layout')

@section('content')

@include('layout.sidebar_nav')

<div class="col">

    <div class="page_title border-bottom my-3 d-flex justify-content-between">
        <h3 class="page_title">Dashboard</h3>

        <div>
            @can('add_edit_projects')
                <a href="{{route('project_edit_page', $project->id)}}" class="btn btn-sm btn-secondary">
                    <i class="bi bi-gear"></i>
                    Settings
                </a>
            @endcan
        </div>
    </div>


    <div class="row row-cols-1 row-cols-md-6 g-2 text-secondary ">

        <div class="col">
            <div class="card base_block border shadow-sm rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-5" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-server"></i> Repositories</span>
                        <b class="fs-5 text-primary">{{$project->repositoriesCount()}}</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card base_block border shadow-sm rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-5" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-stack"></i> Test Suites</span>
                        <b class="fs-5 text-primary">{{$project->suitesCount()}}</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card base_block border shadow-sm rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-5" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-file-earmark-text"></i> Test Cases</span>
                        <b class="fs-5 text-primary">{{$project->casesCount()}}</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card base_block border shadow-sm rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-5" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-robot"></i> Automation</span>
                        <b class="fs-5 text-primary">{{ $project->getAutomationPercent() }}%</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card base_block border shadow-sm rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-5" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-journals"></i> Test Plans</span>
                        <b class="fs-5 text-primary">{{$project->testPlansCount()}}</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card base_block border shadow-sm rounded">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-5" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-play-circle"></i> Test Runs</span>
                        <b class="fs-5 text-primary">{{$project->testRunsCount()}}</b>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="border-bottom my-3">
        <h3 class="page_title">
            Repositories

            @can('add_edit_repositories')
                <a class="mx-3" href="{{route("repository_create_page", $project->id)}}">
                    <button type="button" class="btn btn-sm btn-primary"> <i class="bi bi-plus-lg"></i> Add New</button>
                </a>
            @endcan
        </h3>
    </div>

    <div class="row row-cols-3 g-3">
        @foreach($repositories as $repository)

            <div class="col">
                <div class="card base_block border h-100 shadow-sm rounded">

                    <div class="card-body">
                        <div>
                            <i class="bi bi-stack"></i>
                            <a class="fs-4" href="{{ route('repository_show_page', [$project->id, $repository->id]) }}">{{$repository->title}}</a>
                        </div>

                        @if($repository->description)
                            <div class="card-text text-muted">
                                <span> {{$repository->description}} </span>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end border-top p-2">
                            <span class="text-muted">
                                <b>{{ $repository->suitesCount() }}</b> Test Suites
                                 | <b>{{ $repository->casesCount() }}</b> Test Cases
                                  | <b>{{ $repository->automatedCasesCount() }}</b> Automated
                             </span>
                    </div>

                </div>
            </div>

        @endforeach
    </div>


</div>




@endsection

