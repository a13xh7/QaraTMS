@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="mb-4">
            <h3 class="border-bottom my-3 page-title">Test Runs</h3>

            <div class="d-flex gap-3 mb-3">
                @can('add_edit_test_runs')
                    <a href="{{route("test_run_create_page", $project->id)}}" class="text-decoration-none">
                        <button type="button" class="btn btn-primary px-3 text-nowrap">
                            + New Test Run
                        </button>
                    </a>
                @endcan

                <input type="text" class="form-control" id="testRunFilter" placeholder="Filter by title...">
            </div>
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

                                <div>
                                    <span class="text-muted" title="created at"><strong>Created at:</strong>
                                        {{$testRun->created_at->format('d-m-Y H:i')}}
                                    </span>
                                    <br>
                                    @php
                                        $testPlan = $testPlans->where('id', $testRun->test_plan_id)->first();
                                    @endphp
                                    <span class="text-muted" title="source test plan"><strong>Source Test Plan:</strong>
                                        {{ $testPlan ? $testPlan->title : 'Deleted Test Plan' }}</span>
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

    <script>
        document.getElementById('testRunFilter').addEventListener('input', function () {
            const filterValue = this.value.toLowerCase();
            document.querySelectorAll('.card').forEach(card => {
                const title = card.querySelector('a.fs-4').textContent.toLowerCase();
                card.closest('.col').style.display = title.includes(filterValue) ? '' : 'none';
            });
        });
    </script>
@endsection