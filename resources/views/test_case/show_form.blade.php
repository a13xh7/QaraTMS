<link rel="stylesheet" href="{{ asset_path('css/test_case.css') }}">

<div id="test_case_editor">

    <div class="d-flex justify-content-between mt-2 mb-2" style="align-items: center;">


        <div class="pt-2">

            <span>
                @if($testCase->automated)
                    <i class="bi bi-robot mx-1"></i>
                @else
                    <i class="bi bi-person mx-1"></i>
                @endif
            </span>

            <u class="text-primary">
                <a target="_blank" href="{{route('test_case_show_page', $testCase->id)}}">
                    {{$repository->prefix}}-<span id="tce_case_id">{{$testCase->id}}</span>
                </a>
            </u>
        </div>

        <input type="hidden" id="tce_suite_id" value="{{$testCase->suite_id}}">

        <div class="justify-content-end">

            @can('add_edit_test_cases')
                <button type="button" class="btn btn-warning btn-sm"
                    onclick="renderTestCaseEditForm({{$testCase->id}})"
                    data-bs-toggle="tooltip"
                    data-bs-placement="bottom"
                    data-bs-title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
            @endcan

            @can('delete_test_cases')
                <button type="button" class="btn btn-danger btn-sm"
                    onclick="deleteTestCase({{$testCase->id}})"
                    data-bs-toggle="tooltip"
                    data-bs-placement="bottom"
                    data-bs-title="Delete">
                    <i class="bi bi-trash3"></i>
                </button>
            @endcan

            <!-- TEMPORARILY DISABLED -->
            <!-- <button href="button" class="btn btn-outline-dark btn-sm" onclick="renderTestCaseOverlay({{$testCase->id}})">
                <i class="bi bi-arrows-angle-expand"></i>
            </button> -->

            <button href="button" class="btn btn-secondary btn-sm"
                onclick="closeTestCaseEditor()"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom"
                data-bs-title="Close">
                <i class="bi bi-x-lg"></i>
            </button>

        </div>

    </div>

    <div class="test_case_title border-bottom fs-5 mb-3">
        <b>{{$testCase->title}}</b>
    </div>

    <div id="test_case_content" class="position-relative">

        <div class="mx-4">
            <strong class="fs-6 mb-5 pb-3">Details Test Case</strong>

            <div class="row">
                <div class="col-md-6">
                    <div class="test-case-card">
                        <span>Test Type</span>
                        <span class="test-case-status">
                            @if($testCase->automated)
                                Automated
                            @else
                                Manual
                            @endif
                        </span>
                    </div>
                    <div class="test-case-card">
                        <span>Platform</span>
                        <span class="test-case-status">
                            @if($platform->android)
                                <i class="bi bi-android2 text-success" data-bs-toggle="tooltip" data-bs-title="Android"></i>
                            @endif
                            @if($platform->ios)
                                @if($platform->android) @endif
                                <i class="bi bi-apple text-secondary" data-bs-toggle="tooltip" data-bs-title="iOS"></i>
                            @endif
                            @if($platform->web)
                                @if($platform->android || $platform->ios) @endif
                                <i class="bi bi-laptop text-primary" data-bs-toggle="tooltip" data-bs-title="Web"></i>
                            @endif
                            @if($platform->mweb)
                                @if($platform->android || $platform->ios || $platform->web) @endif
                                <i class="bi bi-phone text-info" data-bs-toggle="tooltip" data-bs-title="MWeb"></i>
                            @endif
                        </span>
                    </div>
                    <div class="test-case-card">
                        <span>Priority</span>
                        <span class="test-case-status">{!! $testCase->priority !!}</span>
                    </div>
                    <div class="test-case-card">
                        <span>Regression</span>
                        <span class="test-case-status" {{ $testCase->regression == 0 ? 'no' : 'yes' }}">
                            <span class="test-case-icon">
                                @if($testCase->regression == 0)
                                    <i class="bi bi-x-circle-fill text-danger" data-bs-toggle="tooltip" data-bs-title="No"></i>
                                @else
                                    <i class="bi bi-check-circle-fill text-success" data-bs-toggle="tooltip" data-bs-title="Yes"></i>
                                @endif
                            </span>
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="test-case-card">
                        <span>Epic Link</span>
                        <span class="test-case-status">
                            <a href="https://admin.atlassian.net/browse/{!! $testCase->epic_link !!}" target="_blank"
                                rel="noopener noreferrer">{!! $testCase->epic_link !!}</a>
                        </span>
                    </div>
                    <div class="test-case-card">
                        <span>Fix Version</span>
                        <span class="test-case-status">{!! $testCase->release_version !!}</span>
                    </div>
                    <div class="test-case-card">
                        <span>Severity</span>
                        <span class="test-case-status">{!! $testCase->severity !!}</span>
                    </div>
                    <div class="test-case-card">
                        <span>Linked Issue</span>
                        <span class="test-case-status">
                            @if(isset($testCase->linked_issue) && !empty($testCase->linked_issue))
                                @php
                                    // Clean the entire input string first: replace all whitespace with a single space and trim
                                    $cleanedInput = trim(preg_replace('/\s+/', ' ', $testCase->linked_issue));

                                    // Split the cleaned string by comma and space
                                    $issueKeys = explode(', ', $cleanedInput);
                                    $linkedIssuesHtml = [];
                                @endphp
                                @foreach($issueKeys as $issueKey)
                                    @php
                                        // Trim any remaining whitespace from individual keys
                                        $trimmedIssueKey = trim($issueKey);
                                    @endphp
                                    @if(!empty($trimmedIssueKey))
                                        @php
                                            // Assuming a base Jira URL structure. Adjust if yours is different.
                                            $jiraUrl = "https://admin.atlassian.net/browse/" . $trimmedIssueKey;
                                        @endphp
                                        @php
                                            // Store the generated link HTML in an array
                                            $linkedIssuesHtml[] = "<a href=\"{$jiraUrl}\" target=\"_blank\" rel=\"noopener noreferrer\">{$trimmedIssueKey}</a>";
                                        @endphp
                                    @endif
                                @endforeach
                                {{-- Join the array of links with a comma and space --}}
                                {!! implode(', ', $linkedIssuesHtml) !!}
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="mx-4">
            <strong class="fs-6">Labels</strong>
            <div class="row mt-1 mb-3 border rounded">
                <div id="tce_labels" class="d-flex flex-wrap">
                    @if(isset($testCase->labels))
                        @php
                            $labelsArray = explode(';', $testCase->labels);
                        @endphp
                        @foreach($labelsArray as $label)
                            <span class="badge me-1">{{ trim($label) }}</span>
                        @endforeach
                    @else
                        <span class="badge badge-none me-1" id="default_label">None</span>
                    @endif
                </div>
            </div>

            <strong class="fs-6">Description</strong>
            <div class="row mt-1 mb-3 border rounded">
                {!! isset($testCase->description) && !empty($testCase->description) ? $testCase->description : ' - ' !!}
            </div>

            <strong class="fs-6">Preconditions</strong>
            <div class="row mt-1 mb-3 border rounded">
                {!! $data->preconditions !!}
            </div>

            <strong class="fs-6">BDD Scenarios</strong>
            <div class="row mt-1 mb-3 border rounded">
                {!! $data->scenarios !!}
            </div>

            <!-- CURRENTLY DISABLED BECAUSE THE TC FORMAT USES BDD, BUT THERE IS A POSSIBILITY TO CHOOSE THE FORMAT TYPE AS EITHER BDD OR STEPS -->
            <!-- @if(isset($data->steps) && !empty($data->steps))
                <strong class="fs-5 pb-3">Steps</strong>

                <div class="row mt-1 mb-2 border p-2 rounded" id="steps_container">

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
                                    @if(isset($step->result))
                                        {!! $step->result !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            @else
                <p>No additional details available.</p>
            @endif -->
        </div>

        <div class="information-container mb-5">
            <div class="info-item">
                <span class="info-label">Created By</span>
                <span class="info-value">{!! $testCase->created_by !!}</span>
                <span class="datetime">{!! $testCase->created_at !!}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Updated By</span>
                <span class="info-value">{!! $testCase->updated_by !!}</span>
                <span class="datetime">{!! $testCase->updated_at !!}</span>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/test_case.js') }}"></script>
