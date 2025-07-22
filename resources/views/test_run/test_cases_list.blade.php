<div class="tree_suite">

    <form method="GET" class="mb-2 flex-shrink align-items-end gap-2">
        <div>
            <label for="statusFilter">Status:</label>
            <select name="status" id="statusFilter" class="form-select form-select-sm select2">
                <option value="">All</option>
                <option value="TODO" {{ request('status') == 'TODO' ? 'selected' : '' }}>To Do</option>
                <option value="PASSED" {{ request('status') == 'PASSED' ? 'selected' : '' }}>Passed</option>
                <option value="FAILED" {{ request('status') == 'FAILED' ? 'selected' : '' }}>Failed</option>
                <option value="BLOCKED" {{ request('status') == 'BLOCKED' ? 'selected' : '' }}>Blocked</option>
                <option value="SKIPPED" {{ request('status') == 'SKIPPED' ? 'selected' : '' }}>Skipped</option>
            </select>
        </div>
        <div>
            <label for="assigneeFilter">Assignee:</label>
            <select name="assignee" id="assigneeFilter" class="form-select form-select-sm select2">
                <option value="">All</option>
                @foreach($users as $assignee)
                    <option value="{{ $assignee->id }}" {{ request('assignee') == $assignee->id ? 'selected' : '' }}>
                        {{ $assignee->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Apply</button>
    </form>

    @foreach($suites as $testSuite)
        @php
            $list_testcase = [];
            $statusFilter = request('status');
            $assigneeFilter = request('assignee');
        @endphp

        @foreach($testSuite->testCases->sortBy('order') as $testCase)
            @php
                $caseStatus = $results[$testCase->id] ?? \App\Enums\TestRunCaseStatus::TODO;
                $user = $users->where('id', $testAssignee[$testCase->id] ?? null)->first();
                $showStatus = empty($statusFilter) || $caseStatus == constant('\App\Enums\TestRunCaseStatus::' . $statusFilter);
                $showAssignee = empty($assigneeFilter) || (isset($testAssignee[$testCase->id]) && $testAssignee[$testCase->id] == $assigneeFilter);
                
                $show = $showStatus && $showAssignee;

                if($show && in_array($testCase->id, $testCasesIds)) {
                    $list_testcase[] = $testCase;
                }
                    
            @endphp

        @endforeach

        {{--   SHOW CHILD SUITE TITLE WITH FULL PATH --}}
        <div style="background: #7c879138; padding-left: 5px; padding-bottom: 5px; border: 1px solid lightgray; border-radius: 3px">
            <i class="bi bi-folder2 fs-5"></i>

            <span class="text-muted" style="font-size: 14px">
                @foreach($testSuite->ancestors()->get()->reverse() as $parent)
                    {{$parent->title}}
                    <i class="bi bi-arrow-right-short"></i>
                @endforeach
            </span>
            <span>{{$testSuite->title}}</span>
            <span class="total-tcs-testrun">{{ count($list_testcase) }} Test Cases</span>
        </div>

        <div class="tree_suite_test_cases">
            @foreach($list_testcase as $testCase)

                <div class="tree_test_case tree_test_case_content py-1 ps-1"
                        onclick="loadTestCase({{$testRun->id}}, {{$testCase->id}})">

                    <div class='d-flex justify-content-between align-items-center'>
                        <div class="d-flex align-items-center" style="min-width:0;">
                            <span>
                                @if($testCase->automated)
                                    <i class="bi bi-robot"></i>
                                @else
                                    <i class="bi bi-person"></i>
                                @endif
                            </span>
                            @php
                                $repo = $repository->where('id', $testSuite->repository_id)->first();
                            @endphp
                            <span class="text-muted ps-1 pe-2">{{$repo ? $repo->prefix : 'ERR'}}-{{$testCase->id}}</span>
                            <span class="text-truncate" style="max-width: 350px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{$testCase->title}}
                            </span>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <div class="result_badge" data-test_case_id="{{$testCase->id}}">
                                @if(isset($results[$testCase->id]))
                                    @if($results[$testCase->id] == \App\Enums\TestRunCaseStatus::TODO)
                                        <span class="badge bg-secondary">To Do</span>
                                    @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::PASSED)
                                        <span class="badge bg-success">Passed</span>
                                    @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::FAILED)
                                        <span class="badge bg-danger">Failed</span>
                                    @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::BLOCKED)
                                        <span class="badge bg-warning">Blocked</span>
                                    @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::SKIPPED)
                                        <span class="badge bg-info">Skipped</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary">To Do</span>
                                @endif
                            </div>
                            <select id="selected_assignee"
                                    class="form-select form-select-sm select2 selected_assignee"
                                    style="width: 110px;"
                                    data-testcase="{{ $testCase->id }}"
                                    data-testrun="{{ $testRun->id }}">
                                @foreach($users as $assignee)
                                    <option value="{{ $assignee->id }}"
                                        {{ (isset($testAssignee[$testCase->id]) && $testAssignee[$testCase->id] == $assignee->id) ? 'selected' : '' }}>
                                        {{ $assignee->name }}
                                    </option>
                                @endforeach
                                <option value="" {{ empty($testAssignee[$testCase->id]) ? 'selected' : '' }}>Unassigned</option>
                            </select>
                        </div>
                    </div>

                </div>

            @endforeach
        </div>

    @endforeach

</div>
