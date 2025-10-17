@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    {{--    TEST SUITES TREE COLUMN--}}
    <div class="col shadow-sm" style="max-width: 700px">

        {{-- COLUMN header--}}
        <div class="border-bottom mt-2 pb-2 mb-2 d-flex justify-content-between">
          <span class="fs-5">
              {{ __('ui.test_run') }}  <i class="bi bi-arrow-right-short"></i> {{$testRun->title}}
          </span>

            <div>
                {{-- Export to PDF button --}}
                <a href="{{route('test_run_export_pdf', [$testRun->project_id, $testRun->id])}}"
                   class="btn btn-sm btn-outline-success me-1"
                   title="{{ __('ui.export_pdf') }}">
                    <i class="bi bi-file-earmark-pdf"></i> {{ __('ui.export_pdf') }}
                </a>

                @can('add_edit_test_runs')
                    <a href="{{route('test_run_edit_page', [$testRun->project_id, $testRun->id])}}"
                       class="btn btn-sm btn-outline-dark me-1"
                       title="{{ __('ui.settings') }}">
                        <i class="bi bi-gear"></i>
                    </a>
                @endcan
            </div>

        </div>

        <div class="pb-2" id="chart">
            @include('test_run.chart')
        </div>

        <div id="tree">
            {{--            @include('test_run.tree')--}}
            @include('test_run.test_cases_list')
        </div>

    </div>


    <div class="col" id="test_case_col">

        <div class="fs-5 border-bottom mt-2 pb-2 mb-2">
            {{ __('ui.select_test_case') }}
        </div>

    </div>

@endsection

@section('footer')

    <script src="{{asset('js/test_run.js')}}"></script>

    <script>
        $(".badge.bg-secondary").first().click(); // select first untested case
    </script>

@endsection
