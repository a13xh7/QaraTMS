<div id="test_case_editor">

    <div class="d-flex justify-content-between border-bottom mt-2 pb-2 mb-2">



        <div style="min-width: 140px">

            <i class="bi bi-chevron-double-up text-danger"></i>

            <span>
                    @if($testCase->automated)
                    <i class="bi bi-robot mx-1"></i>
                @else
                    <i class="bi bi-person mx-1"></i>
                @endif
                </span>

            <span class="text-primary">
                {{$project->prefix}}-
                <span id="tce_case_id">{{$testCase->id}}</span>
            </span>


        </div>


        <div class="test_case_title">
            <a href="{{route('test_case_show_page', $testCase->id)}}" class="link-dark"> <b>{{$testCase->title}}</b> </a>
        </div>

        <div style="min-width: 70px" class="justify-content-end ">
            <button type="button" class="btn btn-outline-dark btn-sm"
                    onclick="renderTestCaseEditForm({{$testCase->id}})">
                <i class="bi bi-pencil"></i>
            </button>

            <button type="button" class="btn btn-outline-dark btn-sm" onclick="closeTestCaseEditor()">
                <i class="bi bi-x-lg"></i>
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
