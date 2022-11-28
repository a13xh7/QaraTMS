
@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3 d-flex justify-content-between">

            <h3 class="page_title">
                @if($testCase->priority == \App\Enums\CasePriority::LOW)
                    <i class="bi bi-chevron-double-down text-warning"></i>
                @elseif($testCase->priority == \App\Enums\CasePriority::NORMAL)
                    <i class="bi bi-list text-info"></i>
                @elseif($testCase->priority == \App\Enums\CasePriority::HIGH)
                    <i class="bi bi-chevron-double-up text-danger"></i>
                @endif

                <span>
                    @if($testCase->automated)
                        <i class="bi bi-robot mx-1"></i>
                    @else
                        <i class="bi bi-person mx-1"></i>
                    @endif
                </span>

                <span class="text-primary">
                     {{$repository->prefix}}-<span id="tce_case_id">{{$testCase->id}}</span>
                </span>
                {{$testCase->title}}

            </h3>

            <button type="button" class="btn btn-outline-dark btn-sm mb-2"
                    onclick="renderTestCaseEditForm({{$testCase->id}})">
                <i class="bi bi-pencil px-1"></i>
            </button>

        </div>

        <div id="test_case_editor">

            <div id="test_case_content" class="position-relative">
                <div class="p-4 pt-0">

                    @if(isset( $data->preconditions) && !empty($data->preconditions) )
                        <strong class="fs-5 pb-3">Preconditions</strong>
                        <div class="row mb-3 border p-3 rounded">

                            <div>
                                {!! $data->preconditions !!}
                            </div>

                        </div>
                    @endif

                    @if(isset($data->steps) && !empty($data->steps))
                        <strong class="fs-5 pb-3">Steps</strong>
                        <div class="row mb-3 border p-3 rounded" id="steps_container">


                            <div class="row step pb-2 mb-2">
                                <div class="col-6">
                                    <b>Action</b>
                                </div>
                                <div class="col-6">
                                    <b>Expected result</b>
                                </div>
                            </div>

                            @foreach($data->steps as $id => $step)
                                <div class="row step border-top mb-2 pt-2" data-badge="{{$id+1}}">

                                    <div class="col-6">
                                        <div>
                                            @if(isset($step->action))
                                                {!! $step->action !!}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div>
                                            @if(isset($step->action))
                                                {!! $step->result !!}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @else
                        <p>No additional details available.</p>
                    @endif

                </div>
            </div>
        </div>

    </div>
@endsection



