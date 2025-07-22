<div class="tree_suite">

    <div class="tree_test_suite_content d-flex justify-content-start">
        <div class="form-check" style="margin-top: 0.5%;">
            <input class="form-check-input test_suite_cbx" type="checkbox" value=""
                data-test_suite_id="{{$testSuite->id}}" data-parent_id="{{$testSuite->parent_id}}">
        </div>

        <div>
            <i class="bi bi-folder2 fs-4"></i>
            <span><button class="suiteTitle collapsible" type="button" onclick="toggleTestSuiteById({{$testSuite->id}})"
                    data-test_suite_id="{{$testSuite->id}}">{{$testSuite->title}}
                    {{$testSuite->testCases->count()}}</button></span>
            <div class=" tree_suite_test_cases" style="display: block; width: 100%;">
                @foreach($testSuite->testCases->sortBy('order') as $testCase)

                    <div class="tree_test_case tree_test_case_content">
                        <div class='tree_test_case_click d-flex justify-content-start'>
                            <div class="form-check">
                                <input class="form-check-input test_case_cbx" type="checkbox" value=""
                                    data-test_suite_id="{{$testSuite->id}}" data-test_case_id="{{$testCase->id}}">
                            </div>

                            <div>
                                <span>@if($testCase->automated)
                                    <i class="bi bi-robot"></i>
                                @else
                                    <i class="bi bi-person"></i>
                                @endif </span>
                                <span class="text-muted ps-2 test_case_id">{{$prefix}}-{{$testCase->id}}</span>
                                <span>{{$testCase->title}}</span>
                            </div>
                        </div>
                    </div>

                @endforeach
            </div>
        </div>
    </div>

    @foreach($testSuite->children as $testSuite)
        @include('test_plan.tree_item')
    @endforeach

    @section('footer')
        <script src="{{ asset_path('js/test_plan_page.js') }}"></script>
    @endsection
</div>