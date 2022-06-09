@extends('layout.base_layout')

@section('content')

@include('layout.sidebar_nav')

<div class="col">

    <div class="page_title border-bottom my-3 d-flex justify-content-between">
        <h3 class="page_title">
            Dashboard
        </h3>

        <div>
            <a href="{{route('project_edit_page', $project->id)}}" class="btn btn-sm btn-secondary">
                <i class="bi bi-gear"></i>
                Settings
            </a>
        </div>
    </div>


    <div class="row row-cols-1 row-cols-md-4 g-3">

        <div class="col">
            <div class="base_block border shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-3" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-server"></i> REPOSITORIES</span>
                        <b class="fs-1 text-primary">{{$project->repositoriesCount()}}</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="base_block border shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-3" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-stack"></i> TEST SUITES</span>
                        <b class="fs-1 text-primary">{{$project->suitesCount()}}</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="base_block border shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-3" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-file-earmark-text"></i> TEST CASES</span>
                        <b class="fs-1 text-primary">{{$project->casesCount()}}</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="base_block border shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-3" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-robot"></i> AUTOMATION</span>
                        <b class="fs-1 text-primary">{{ $project->getAutomationPercent() }}%</b>
                    </div>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="base_block border shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-3" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-journals"></i> TEST PLANS</span>
                        <b class="fs-1 text-primary">{{$project->testPlansCount()}}</b>
                    </div>
                </div>
            </div>
        </div>


        <div class="col">
            <div class="base_block border shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span class="fs-3" style="margin-top: auto; margin-bottom: auto"><i class="bi bi-play-circle"></i> TEST RUNS</span>
                        <b class="fs-1 text-primary">{{$project->testRunsCount()}}</b>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <h3 class="page_title my-5 pb-3 border-bottom">
        Latest Test Runs
    </h3>

    <div class="row row-cols-1 row-cols-md-1 g-3">
        @foreach($testRuns as $testRun)

            <div class="col">
                <div class="card h-100">

                    <div class="card-body d-flex justify-content-between ">
                        <div>
                            <a class="fs-4" href="{{route('test_run_show_page', [$project->id, $testRun->id])}}">
                                <i class="bi bi-play-circle"></i> {{$testRun->title}}
                            </a>
                        </div>

                        <div>
                            <span class="text-muted" title="created at">{{$testRun->created_at->format('d-m-Y H:i')}} </span>
                        </div>
                    </div>

                    <div class="border-top p-2">


                        @include('test_run.chart')

                    </div>

                </div>
            </div>
        @endforeach
    </div>


</div>
@endsection

{{--    <div>--}}
{{--        <button type="submit" class="btn btn-light" title="Delete" style="padding: 0 7px 0 7px;">--}}
{{--            <i class="bi bi-trash3" style="font-size: 13px;"></i>--}}
{{--        </button>--}}

{{--        <button type="submit" class="btn btn-light" title="Edit" style="padding: 0 7px 0 7px;">--}}
{{--            <i class="bi bi-pencil" style="font-size: 13px;"></i>--}}
{{--        </button>--}}

{{--        <button type="submit" class="btn btn-light" title="Settings" style="padding: 0 7px 0 7px;">--}}
{{--            <i class="bi bi-gear" style="font-size: 13px;"></i>--}}
{{--        </button>--}}
{{--    </div>--}}
