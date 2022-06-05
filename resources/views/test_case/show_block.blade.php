<div id="test_case_block">

    <div class="d-flex justify-content-between border-bottom mt-2 pb-2 mb-2">
        <div>
            <span class="fs-6 badge bg-secondary">{{$project->prefix}}-{{$testCase->id}}</span>

            <span class="fs-5">
            @if($testCase->automated)
                    <i class="bi bi-robot"></i>
                @else
                    <i class="bi bi-person"></i>
                @endif
        </span>

            <span class="fs-6">{{$testCase->title}}</span>
        </div>

        <div>
            <button type="button" class="btn btn-outline-dark btn-sm"
                    onclick="renderTestCaseEditForm({{$testCase->id}})">
                <i class="bi bi-pencil"></i> <b>Edit</b>
            </button>

            <button type="button" class="btn btn-outline-dark btn-sm" onclick="closeTestCaseViewer()">
                <i class="bi bi-x-lg"></i> <b>Close</b>
            </button>
        </div>
    </div>


    <div id="test_case_content" class="position-relative">
            <div class="p-4 pt-0">

                @if(isset( $data->preconditions) && !empty($data->preconditions) )
                    <strong class="fs-5 pb-3">Preconditions</strong>
                    <div class="row mb-3 border p-3 rounded">

                        <div>
                            {!! parsedown($data->preconditions) !!}
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
                                        {!! parsedown($step->action) !!}
                                    </div>
                                </div>

                                <div class="col-6">
                                    <div>
                                        {!! parsedown($step->result) !!}
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
