<div class="tree_test_case" data-test_case_id="{{$testCase->id}}">
    <div class='tree_test_case_content d-flex justify-content-between'>

        <div class='tree_test_case_click'
             onclick="renderTestCase('{{$testCase->id}}')">
                        <span>
                            @if($testCase->automated)
                                <i class="bi bi-robot"></i>
                            @else
                                <i class="bi bi-person"></i>
                            @endif
                        </span>
            <span class="text-muted ps-2 test_case_id">{{$repository->prefix}}-{{$testCase->id}}</span>
            <span class="tree_case_title">{{$testCase->title}}</span>
        </div>

        <div class="tree_test_case_controls">
            <button class="btn p-0 px-1" type="button" title="Edit"
                    onclick="renderTestCaseEditForm('{{$testCase->id}}')">
                <i class="bi bi-pencil"></i>
            </button>

            <button class="btn p-0 px-1" type="button" title="Delete" onclick="deleteTestCase({{$testCase->id}})">
                <i class="bi bi-trash3"></i>
            </button>
        </div>
    </div>
</div>
