<div class="tree_suite">

    <div class="tree_test_suite_content d-flex justify-content-start">
        <div>
            <i class="bi bi-folder2 fs-4"></i>
            <span><b class="suiteTitle">{{$testSuite->title}}</b></span>
        </div>
    </div>

    <div class="tree_suite_test_cases">
        @foreach($testSuite->testCases->sortBy('order') as $testCase)

            @if( in_array($testCase->id, $testCasesIds)   )

                <div class="tree_test_case tree_test_case_content py-1 ps-1" onclick="loadTestCase({{$testRun->id}}, {{$testCase->id}})">

                    <div class='d-flex justify-content-between'>

                        <div class="mt-1">
                            <span>@if($testCase->automated) <i class="bi bi-robot"></i> @else <i class="bi bi-person"></i> @endif </span>
                            <span class="text-muted ps-1 pe-3 ">{{$repository->prefix}}-{{$testCase->id}}</span>
                            <span>{{$testCase->title}}</span>
                        </div>

                        <div class="result_badge pt-1 pe-2" data-test_case_id="{{$testCase->id}}">

                            @if(isset($results[$testCase->id]))
                                @if($results[$testCase->id] == \App\Enums\TestRunCaseStatus::NOT_TESTED)
                                    <span class="badge bg-secondary">Not Tested</span>
                                @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::PASSED)
                                    <span class="badge bg-success">Passed</span>
                                @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::FAILED)
                                    <span class="badge bg-danger">Failed</span>
                                @elseif($results[$testCase->id] == \App\Enums\TestRunCaseStatus::BLOCKED)
                                    <span class="badge bg-warning">Blocked</span>
                                @endif

                            @else
                                <span class="badge bg-secondary">Not Tested</span>
                            @endif
                        </div>
                    </div>

                </div>

            @endif
        @endforeach
    </div>

    @foreach($testSuite->children as $testSuite)
        @include('test_run.tree_item')
    @endforeach

</div>

