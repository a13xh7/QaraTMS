<div id="test_case_editor">

    <div class="d-flex justify-content-between border-bottom mt-2 pb-2 mb-2">
        <div>
            <span class="fs-5">Edit New Test Case</span>
        </div>

        <div>
            <button href="button" class="btn btn-outline-dark btn-sm" onclick="renderTestCase({{$testCase->id}})">
                <i class="bi bi-x-lg"></i> <b>Cancel</b>
            </button>
        </div>
    </div>

    <div id="test_case_content">
        <div class="p-4 pt-0">

            <div class="row mb-3 border p-3 rounded">

                <div class="mb-3 d-flex justify-content-start border p-3 bg-light">

                    <div>
                        <label for="test_suite_id" class="form-label">Test Suite</label>
                        <select name="suite_id" id="tce_test_suite_select" class="form-select border-secondary">

                            @foreach($repository->suites as $repoTestSuite)

                                <option value="{{$repoTestSuite->id}}"
                                        @if($repoTestSuite->id == $testCase->suite_id)
                                        selected
                                    @endif
                                >
                                    {{$repoTestSuite->title}}
                                </option>
                            @endforeach

                        </select>
                    </div>


                    <div class="mx-5">

                        <label class="form-label">
                            <b>Priority</b>
                            <i class="bi bi-chevron-double-up text-danger"></i>|<i class="bi bi-list text-info"></i>|<i class="bi bi-chevron-double-down text-warning"></i>
                        </label>

                        <select id="tce_priority_select" name="priority" class="form-select border-secondary" id="tce_priority_select">

                            @if($testCase->priority == \App\Enums\CasePriority::NORMAL)
                                <option value="{{\App\Enums\CasePriority::NORMAL}}" selected> Normal</option>
                                <option value="{{\App\Enums\CasePriority::HIGH}}">High</option>
                                <option value="{{\App\Enums\CasePriority::LOW}}">Low</option>
                            @elseif($testCase->priority == \App\Enums\CasePriority::HIGH)
                                <option value="{{\App\Enums\CasePriority::NORMAL}}"> Normal</option>
                                <option value="{{\App\Enums\CasePriority::HIGH}}" selected>High</option>
                                <option value="{{\App\Enums\CasePriority::LOW}}">Low</option>
                            @else
                                <option value="{{\App\Enums\CasePriority::NORMAL}}"> Normal</option>
                                <option value="{{\App\Enums\CasePriority::HIGH}}">High</option>
                                <option value="{{\App\Enums\CasePriority::LOW}}" selected>Low</option>
                            @endif

                        </select>
                    </div>

                    <div class="mx-5">
                        <label class="form-label">Type <i class="bi bi-person"></i> | <i class="bi bi-robot"></i></label>
                        <select name="automated" class="form-select border-secondary" id="tce_automated_select">

                            @if($testCase->automated)
                                <option value="0"> Manual</option>
                                <option value="1" selected>Automated</option>
                            @else
                                <option value="0" selected> Manual</option>
                                <option value="1">Automated</option>
                            @endif

                        </select>
                    </div>

                </div>

                <input type="hidden" id="tce_case_id" value="{{$testCase->id}}">

                <div class="mb-3">
                    <label for="title" class="form-label"><b>Title</b></label>
                    <input name="title" id="tce_title_input" type="text" class="form-control border-secondary" value="{{$testCase->title}}" >
                </div>

                <div class="col">
                    <label class="form-label"><b>Preconditions</b></label>
                    @if(isset($data->preconditions))
                        <textarea name="pre_conditions" class="form-control border-secondary" id="tce_preconditions_input" rows="3">{{ $data->preconditions }}</textarea>
                    @else
                        <textarea name="pre_conditions" class="form-control border-secondary" id="tce_preconditions_input" rows="3"></textarea>
                    @endif
                </div>

            </div>



            <div class="row mb-3 border p-3 rounded" id="steps_container">
                <p class="fs-5">Steps</p>

                @if(isset($data->steps))

                    @foreach($data->steps as $id => $step)

                        <div class="row m-0 p-0 step">

                            <div class="col-auto p-0 pt-4">
                                <span class="fs-5 step_number">{{$id+1}}</span>
                            </div>

                            <div class="col">
                                <label class="form-label m-0"><b>Action</b></label>
                                <textarea class="form-control border-secondary step_action" rows="2">{!! $step->action !!}</textarea>
                            </div>

                            <div class="col">
                                <label class="form-label m-0"><b>Expected Result</b></label>
                                <textarea class="form-control border-secondary step_result" rows="2">{{ $step->result }}</textarea>
                            </div>

                            <div class="col-auto p-0 pt-4">
                                <button type="button" class="btn btn-outline-danger btn-sm step_delete_btn" onclick="removeStep(this)">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>

                        </div>
                    @endforeach

                @else
                    <div class="row m-0 p-0 step">

                        <div class="col-auto p-0 pt-4">
                            <span class="fs-5 step_number">1</span>
                        </div>

                        <div class="col">
                            <label class="form-label m-0"><b>Action</b></label>
                            <textarea class="form-control border-secondary step_action" rows="2"></textarea>
                        </div>

                        <div class="col">
                            <label class="form-label m-0"><b>Expected Result</b></label>
                            <textarea class="form-control border-secondary step_result" rows="2"></textarea>
                        </div>

                        <div class="col-auto p-0 pt-4">
                            <button type="button" class="btn btn-outline-danger btn-sm step_delete_btn" onclick="removeStep(this)">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>

                    </div>
                @endif
            </div>


            <div class="row border mt-3 p-3 rounded">
                <div class="col">
                    <button type="button" class="btn btn-primary" onclick="addStep()">
                        <i class="bi bi-plus-circle"></i>
                        Add Step
                    </button>
                </div>


                <div class="col">
                    <button id="tce_save_btn" type="button" class="btn btn-warning w-100" onclick="updateTestCase()">
                        <i class="bi bi-save"></i>
                        Update Test Case
                    </button>
                </div>
            </div>

        </div>
    </div>




</div>
