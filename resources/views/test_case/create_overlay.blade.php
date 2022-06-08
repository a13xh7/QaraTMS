<div id="test_case_editor_overlay" class="overlay" style="display: block">

    <div class="card position-absolute top-50 start-50 translate-middle border-secondary shadow" style="width: 90%; "> {{--height: 867px;--}}

        <div id="test_case_editor">

            {{--CASE HEADER--}}
            <div class="px-3 py-2 d-flex justify-content-between" style="background: #f4f6f9">
                <div>
                    <span class="fs-5">Create Test Case</span>
                </div>

                <div>
                    <button href="button" class="btn btn-outline-dark btn-sm" onclick="closeTestCaseEditor()">
                        <i class="bi bi-x-lg"></i> <b>Cancel</b>
                    </button>
                </div>
            </div>
            {{--CASE HEADER--}}

            {{--CASE CONTROLS FOOTER--}}
            <div id="test_case_content">
                <div class="p-4 pt-0">

                    <div class="row p-3 pb-0">

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

                            <div class="mx-5">
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

                        <div class="mb-3">
                            <label for="title" class="form-label"><b>Title</b></label>
                            <input name="title" id="tce_title_input" type="text" class="form-control border-secondary" >
                        </div>

                        <div class="col">
                            <label class="form-label"><b>Preconditions</b></label>
                            <textarea name="pre_conditions" class="editor_textarea" id="tce_preconditions_input" rows="3"></textarea>
                        </div>

                    </div>

                    <div class="row mb-3 p-3" id="steps_container">

                        <div class="mb-3">
                            <b class="fs-5 d-inline-block">Steps </b>
                            <i class="text-muted d-inline-block"> (Action -> Expected Result)</i>
                        </div>

                        <div class="row m-0 p-0 step">

                            <div class="col-auto p-0 pt-4">
                                <span class="fs-5 step_number">1</span>
                            </div>

                            <div class="col">
                                <textarea class="editor_textarea step_action" rows="2"></textarea>
                            </div>

                            <div class="col">
                                <textarea class="editor_textarea step_result" rows="2"></textarea>
                            </div>

                            <div class="col-auto p-0 pt-4">
                                <button type="button" class="btn btn-outline-danger btn-sm step_delete_btn" onclick="removeStep(this)">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            {{--CASE CONTENT--}}

            {{--CASE CONTROLS FOOTER--}}
            <div class="px-3 py-2 d-flex justify-content-between" style="background: #f4f6f9">
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

                    <button id="tce_save_btn" type="button" class="btn btn-success" onclick="createTestCase(true)">
                        Create and add another
                    </button>
                </div>
            </div>
            {{--CASE CONTROLS FOOTER--}}

        </div>
        {{--case editor--}}


    </div> {{--card--}}
</div>  {{--Overlay--}}
