@foreach($testCases as $testCase)

        <div class="test_case border-bottom d-flex ps-1 py-2 justify-content-between" data-case_id="{{$testCase->id}}">

            <div class="d-flex justify-content-start test_case_clickable_area" onclick="renderTestCase('{{$testCase->id}}')">
                <div class="me-1 test_case_info">
                    <i class="bi bi-chevron-double-up text-danger"></i>

                    <span>
                        @if($testCase->automated)
                            <i class="bi bi-robot mx-1"></i>
                        @else
                            <i class="bi bi-person mx-1"></i>
                        @endif
                    </span>

                    <span class="text-primary">{{$project->prefix}}-{{$testCase->id}}</span>
                </div>

                <div class="test_case_title">
                    <span>{{$testCase->title}}</span>
                </div>
            </div>

            <div class="test_case_controls" >
                <button class="btn py-0 px-1" type="button" title="Edit" onclick="renderTestCaseEditForm('{{$testCase->id}}')">
                    <i class="bi bi-pencil"></i>
                </button>

                <button class="btn py-0 px-1" type="button" title="Delete" onclick="deleteTestCase({{$testCase->id}})">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>

        </div>

@endforeach
