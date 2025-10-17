@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                {{ __('ui.test_runs') }}

                @can('add_edit_test_runs')
                    <a class="mx-3" href="{{route("test_run_create_page", $project->id)}}">
                        <button type="button" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> {{ __('ui.new_test_run') }}
                        </button>
                    </a>
                @endcan

            </h3>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-3">
            @foreach($testRuns as $testRun)

                <div class="col">
                    <div class="card base_block shadow h-100 rounded border">

                        <div class="card-body d-flex justify-content-between ">
                            <div>
                                <a class="fs-4" href="{{route('test_run_show_page', [$project->id, $testRun->id])}}">
                                    <i class="bi bi-play-circle"></i> {{$testRun->title}}
                                </a>
                            </div>

                            <div class="d-flex align-items-center">
                                <a href="{{route('test_run_export_pdf', [$project->id, $testRun->id])}}"
                                   class="btn btn-sm btn-outline-success me-2"
                                   title="Export to PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                      <span class="text-muted"
                          title="{{ __('ui.created') }}">{{$testRun->created_at->format('d-m-Y H:i')}} </span>
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
