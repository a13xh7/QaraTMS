<link rel="stylesheet" href="{{ asset_path('css/test_case.css') }}">

<div id="test_case_editor">

    <div class="d-flex justify-content-between border-bottom mt-2 pb-2 mb-2">
        <div>
            <span class="fs-5">Create Test Case</span>
        </div>

        <div>
            <button href="button" class="btn btn-outline-dark btn-sm btn-secondary" onclick="closeTestCaseEditor()">
                <i class="bi bi-x-lg"></i> <b>Cancel</b>
            </button>
        </div>
    </div>

    <div id="test_case_content">
        <div class="p-1 pt-0">

            <div class="row mb-3">

                <div class="mb-3 d-flex justify-content-start border p-3 bg-light">

                    <div>
                        <label for="test_suite_id" class="form-label"><strong>Feature</strong></label>
                        <select name="suite_id" id="tce_test_suite_select" class="form-select border-secondary"
                            disabled>

                            @foreach($repository->suites as $repoTestSuite)
                                <option value="{{$repoTestSuite->id}}" @if($repoTestSuite->id == $parentTestSuite->id)
                                selected @endif>
                                    @if($repoTestSuite->parent_id)
                                        {{$repository->suites->find($repoTestSuite->parent_id)?->title}} -
                                    @endif
                                    {{$repoTestSuite->title}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mx-3">
                        <label class="form-label"><b>Priority</b></label>

                        <select id="tce_priority_select" name="priority" class="form-select border-secondary">
                            <option value="{{\App\Enums\CasePriority::HIGH}}">High</option>
                            <option value="{{\App\Enums\CasePriority::MEDIUM}}" selected>Medium</option>
                            <option value="{{\App\Enums\CasePriority::LOW}}">Low</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label"><b>Test Type</b></label>
                        <select name="automated" class="form-select border-secondary" id="tce_automated_select">
                            <option value="0" selected>Manual</option>
                            <option value="1">Automated</option>
                        </select>
                    </div>

                    <div class="mx-4">
                        <label class="form-label"><b>Regression</b></label>
                        <select name="regression_status" class="form-select border-secondary" id="tce_regression" style="width: 80px">
                            <option value="0" selected>No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 d-flex justify-content-start border p-3 bg-light">

                    <div>
                        <label class="form-label required"><b>Epic Link</b></label>
                        <input type="text" name="epic_link" id="tce_epic_link" class="form-control border-secondary autocomplete"
                            placeholder="Enter epic link..." style="width: 150px;">
                        <div class="text-danger" id="epic_link_error" style="display: none;">Please fill the mandatory epic link!</div>
                    </div>

                    <div class="mx-4">
                        <label class="form-label"><b>Severity</b></label>
                        <select name="severity" class="form-select border-secondary" id="tce_severity">
                            <option value="Critical">Critical</option>
                            <option value="Major">Major</option>
                            <option value="Moderate" selected>Moderate</option>
                            <option value="Minor">Minor</option>
                            <option value="Low">Low</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label border-secondary required"><b>Platform</b></label>
                        <div class="platform-options">
                            <div class="platform-option">
                                <input type="checkbox" name="platform" id="tce_platform_android"
                                    class="form-check-input">
                                <label for="tce_platform_android" class="form-check-label">Android</label>
                            </div>
                            <div class="platform-option">
                                <input type="checkbox" name="platform" id="tce_platform_ios" class="form-check-input">
                                <label for="tce_platform_ios" class="form-check-label">iOS</label>
                            </div>
                            <div class="platform-option">
                                <input type="checkbox" name="platform" id="tce_platform_mweb" class="form-check-input">
                                <label for="tce_platform_mweb" class="form-check-label">Mweb</label>
                            </div>
                            <div class="platform-option">
                                <input type="checkbox" name="platform" id="tce_platform_web" class="form-check-input">
                                <label for="tce_platform_web" class="form-check-label">Web</label>
                            </div>
                        </div>
                        <div class="text-danger" id="platform_error" style="display:none;">At least one platform must be selected!</div>
                    </div>
                </div>

                <div class="mb-3 d-flex justify-content-start border p-3 bg-light">
                    <div>
                        <label class="form-label"><b>Linked Issue</b></label>
                        <input type="text" name="linked_issue" id="tce_linked_issue"
                            class="form-control border-secondary autocomplete" placeholder="Enter linked issue..."
                            style="width: 150px">
                    </div>

                    <div class="mx-4">
                        <label class="form-label"><b>Fix Version</b></label>
                        <input type="text" name="release_version" id="tce_release_version"
                            class="form-control border-secondary autocomplete" placeholder="Enter version..."
                            style="width: 110px">
                    </div>

                    <div class="mb-3 p-0">
                        <label for="labels" class="form-label required"><b>Labels</b></label>
                        <div id="label_input_container" onclick="focusInput()">
                            <div id="tce_labels" class="d-flex flex-wrap me-2">
                                <span class="badge badge-none me-1" id="default_label">None</span>
                            </div>
                            <input class="autocomplete" type="text" id="tce_label_input" placeholder="Enter a label..." 
                                oninput="showSuggestions()" onkeypress="addLabel(event)" style="display: none;">
                            <div class="suggestion-box" id="tce_label_suggestion" style="display: none;"></div>
                        </div>
                        <div class="text-danger" id="empty_label_error" style="display: none;">Please add at least one label!</div>
                    </div>
                </div>

                <div class="mb-3 p-0">
                    <label for="title" class="form-label required"><b>Title</b></label>
                    <input name="title" id="tce_title_input" placeholder="Please enter a title..."
                        type="text" class="form-control border-secondary autocomplete">
                    <div class="text-danger" id="title_error" style="display:none;">Please fill the mandatory title!</div>
                </div>

                <div class="mb-3 p-0">
                    <label for="description" class="form-label"><b>Description</b></label>
                    <textarea name="description" id="tce_desc_input" placeholder="Please enter a description..." 
                        class="form-control border-secondary autocomplete" rows="3"></textarea>
                </div>

                <div class="mb-4 p-0">
                    <label class="form-label required"><b>Preconditions</b></label>
                    <div class="btn-group" role="group" aria-label="Precondition Type">
                        <input type="radio" class="btn-check" name="precond_type" id="precond_free_text" value="free_text" autocomplete="off"
                            {{ old('precond_type', 'free_text') == 'free_text' ? 'checked' : '' }}
                            onchange="handlePreconditionTypeChange(this.value)">
                        <label class="btn btn-outline-primary" for="precond_free_text" onclick="document.getElementById('precond_free_text').checked = true; handlePreconditionTypeChange('free_text')">Free text</label>

                        <input type="radio" class="btn-check" name="precond_type" id="precond_from_cases" value="from_cases" autocomplete="off"
                            {{ old('precond_type', '') == 'from_cases' ? 'checked' : '' }}
                            onchange="handlePreconditionTypeChange(this.value)">
                        <label class="btn btn-outline-primary" for="precond_from_cases" onclick="document.getElementById('precond_from_cases').checked = true; handlePreconditionTypeChange('from_cases')">Get from other test cases</label>
                    </div>

                    <!-- Free text area -->
                    <div id="precond_textarea_wrap" class="mt-2" style="display: {{ old('precond_type', 'free_text') == 'free_text' ? 'block' : 'none' }}">
                        <textarea name="pre_conditions" class="editor_textarea form-control border-secondary"
                            id="tce_preconditions_input" rows="3">{{ old('pre_conditions') }}</textarea>
                        <div class="text-danger" id="pre_conditions_error" style="display:none;">Please fill the mandatory pre-conditions!</div>
                    </div>

                    <!-- Button to open popup for test case selection -->
                    <div id="precond_select_cases_wrap" class="mt-2" style="display: {{ old('precond_type', '') == 'from_cases' ? 'block' : 'none' }}">
                        <button type="button" class="btn btn-outline-primary" onclick="openTestCasePopup()">Select Test Cases</button>
                        <div id="selected_test_cases" class="mt-2">
                            {{-- Selected test cases will be displayed here by JavaScript --}}
                        </div>
                    </div>
                </div>

                <div class="mb-4 p-0">
                    <label class="form-label required"><b>BDD Scenarios</b></label>
                    <textarea name="bdd_scenarios" id="tce_bdd_scenarios_input" 
                        class="editor_textarea form-control border-secondary" rows="3"></textarea>
                    <div class="text-danger" id="bdd_scenarios_error" style="display:none;">Please fill the mandatory BDD scenarios!</div>
                </div>
            </div>

            <!-- CURRENTLY DISABLED BECAUSE THE TC FORMAT USES BDD, BUT THERE IS A POSSIBILITY TO CHOOSE THE FORMAT TYPE AS EITHER BDD OR STEPS -->
            <!-- <div class="row" id="steps_container">
                <div class="p-0 mb-1">
                    <b class="fs-5">Steps</b>
                    <span class="text-muted" style="font-size: 12px">Action <i class="bi bi-arrow-right"></i> Expected Result</span>
                </div>

                <div class="row m-0 p-0 mt-2 step">
                    <div class="col-auto p-0 d-flex flex-column align-items-center">
                        <span class="fs-5 step_number">1</span>

                        <button type="button" class="btn btn-outline btn-sm step_delete_btn px-1 py-0"
                                onclick="stepUp(this)">
                            <i class="bi bi-arrow-up-circle"></i>
                        </button>

                        <button type="button" class="btn btn-outline-danger btn-sm step_delete_btn px-1 py-0"
                                onclick="removeStep(this)">
                            <i class="bi bi-x-circle"></i>
                        </button>

                        <button type="button" class="btn btn-outline btn-sm step_delete_btn px-1 py-0"
                                onclick="stepDown(this)">
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
            </div> -->
        </div>
    </div>

    <div id="test_case_editor_footer" class="col-5 d-flex justify-content-between pt-2">
        <!-- CURRENTLY DISABLED BECAUSE THE TC FORMAT USES BDD, BUT THERE IS A POSSIBILITY TO CHOOSE THE FORMAT TYPE AS EITHER BDD OR STEPS -->
        <!-- <div class="col">
            <button type="button" class="btn btn-primary" onclick="addStep()">
                <i class="bi bi-plus-circle"></i>
                Add Step
            </button>
        </div> -->

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

<!-- Test Case Selection Modal -->
<div class="modal" tabindex="-1" id="testCaseModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testCaseModalLabel">Select Test Cases</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="closeTestCasePopup()"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="testCaseSearch" class="form-control mb-2" placeholder="Search by ID or Title..." oninput="filterTestCases()">
                <form id="testCaseSelectForm">
                    <div class="list-group">
                        @foreach($allTestCases as $tc)
                            <label class="list-group-item test-case-item">
                                <input class="form-check-input me-1"
                                       type="checkbox"
                                       value="{{ $tc->id }}"
                                       data-title="{{ $tc->title }}"
                                       data-prefix="{{ $tc->suite->repository->prefix }}">
                                <span>
                                    <b>{{ $tc->suite->repository->prefix }}-{{ $tc->id }}</b>: {{ $tc->title }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </form>
                <div id="testCaseSelectionError" class="text-danger">Please select at least one test case.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="submitTestCaseSelection()">Submit</button>
                <button type="button" class="btn btn-secondary" onclick="closeTestCasePopup()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/test_case.js') }}"></script>
