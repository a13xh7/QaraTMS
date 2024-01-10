<div id="test_case_editor">

    <div class="d-flex justify-content-between border-bottom mt-2 pb-2 mb-2">
        <div>
            <span class="fs-5">Create Test Case</span>
        </div>

        <div>
            <button href="button" class="btn btn-outline-dark btn-sm" onclick="closeTestCaseEditor()">
                <i class="bi bi-x-lg"></i> <b>Cancel</b>
            </button>
        </div>
    </div>

    <div id="test_case_content">
        <div class="p-4 pt-0">

            <div class="row mb-3">

                <div class="mb-3 d-flex justify-content-start border p-3 bg-light">

                    <div>
                        <label for="test_suite_id" class="form-label"><strong>Test Suite</strong></label>
                        <select name="suite_id" id="tce_test_suite_select" class="form-select border-secondary">

                            @foreach($repository->suites as $repoTestSuite)
                                <option value="{{$repoTestSuite->id}}"
                                        @if($repoTestSuite->id == $parentTestSuite->id)
                                        selected
                                    @endif
                                >
                                    {{$repoTestSuite->title}}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="mx-4">
                        <label class="form-label">
                            <b>Priority</b>
                            <i class="bi bi-chevron-double-up text-danger"></i>|<i class="bi bi-list text-info"></i>|<i class="bi bi-chevron-double-down text-warning"></i>
                        </label>

                        <select id="tce_priority_select" name="priority" class="form-select border-secondary">
                            <option value="{{\App\Enums\CasePriority::NORMAL}}" selected> Normal</option>
                            <option value="{{\App\Enums\CasePriority::HIGH}}">High</option>
                            <option value="{{\App\Enums\CasePriority::LOW}}">Low</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label"><b>Type</b> <i class="bi bi-person"></i> | <i class="bi bi-robot"></i></label>
                        <select name="automated" class="form-select border-secondary" id="tce_automated_select">
                            <option value="0" selected> Manual</option>
                            <option value="1">Automated</option>
                        </select>
                    </div>

                </div>

                <div class="mb-3 p-0">
                    <label for="title" class="form-label"><b>Title</b></label>
                    <input name="title" id="tce_title_input" type="text" class="form-control border-secondary" autofocus>
                </div>

                <div class="col p-0">
                    <label class="form-label"><b>Preconditions</b></label>
                    <textarea name="pre_conditions" class="editor_textarea form-control border-secondary" id="tce_preconditions_input" rows="3"></textarea>
                </div>

            </div>

            <div class="row" id="steps_container">
               <div class="p-0 mb-1">
                   <b class="fs-5">Steps</b>
                   <span class="text-muted" style="font-size: 12px">Action <i class="bi bi-arrow-right"></i> Expected Result</span>
               </div>

                <div class="row m-0 p-0 mt-2 step">
                    <div class="col-auto p-0 d-flex flex-column align-items-center">
                        <span class="fs-5 step_number">1</span>

                        <button type="button" class="btn btn-outline btn-sm step_delete_btn px-1 py-0" onclick="stepUp(this)">
                            <i class="bi bi-arrow-up-circle"></i>
                        </button>

                        <button type="button" class="btn btn-outline-danger btn-sm step_delete_btn px-1 py-0" onclick="removeStep(this)">
                            <i class="bi bi-x-circle"></i>
                        </button>

                        <button type="button" class="btn btn-outline btn-sm step_delete_btn px-1 py-0" onclick="stepDown(this)">
                            <i class="bi bi-arrow-down-circle"></i>
                        </button>
                    </div>

                    <div class="col p-0 px-1 test_case_step">
                        <textarea class="editor_textarea form-control border-secondary step_action" rows="2"></textarea>
                    </div>
                    <div class="col p-0 test_case_step">
                        <textarea class="editor_textarea form-control border-secondary step_result" rows="2"></textarea>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <div id="test_case_editor_footer" class="col-5 d-flex justify-content-between border-top pt-2">
        <div class="col">
            <button type="button" class="btn btn-primary" onclick="addStep()">
                <i class="bi bi-plus-circle"></i>
                Add Step
            </button>
        </div>

        <div class="col d-flex justify-content-end pe-3">

            <button id="tce_save_btn" type="button" class="btn btn-success me-3" onclick="createTestCase()">
                Create
            </button>

            <button id="tce_save_btn" type="button" class="btn btn-success me-3" onclick="createTestCase(true)">
                Create and add another
            </button>
        </div>
    </div>

</div>
