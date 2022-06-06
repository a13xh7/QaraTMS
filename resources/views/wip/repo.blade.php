@extends('layout.base_layout')

@section('head')
    <link rel="stylesheet" href="{{asset('repository/repo_wip.css')}}">
    <link rel="stylesheet" href="{{asset('repository/tree.css')}}">
@endsection

@section('content')

    {{--    TEST SUITES TREE COLUMN--}}
    <div class="col-auto shadow" id="suites_tree_col">

        {{-- COLUMN header--}}
        <div class="border-bottom mt-2 pb-2 mb-2 d-flex justify-content-between">
            <span class="fs-5">
               Test Suites Tree
            </span>

            <div>
                <a href="{{route('repository_edit_page', [$project->id, $repository->id])}}"
                   class="btn btn-sm btn-outline-dark me-1"
                   title="Repository Settings">
                    <i class="bi bi-gear"></i>
                </a>

                <button id="add_root_suite_btn" class="btn btn-primary btn-sm" type="button" title="Add Test Suite">
                    <i class="bi bi-plus-lg"></i> Test Suite
                </button>

            </div>
        </div>

        <button type="button" class="btn btn-outline-dark btn-sm w-100">ROOT</button>

        <ul id="tree">
            <li></li>
        </ul>

    </div>


    <div id="test_cases_col" class="col">
        {{-- COLUMN header--}}
        <div class="border-bottom mt-2 pb-2 mb-2 d-flex justify-content-between">
            <span class="fs-5">
               Test Cases
            </span>

            <div>
                <button id="add_root_suite_btn" class="btn btn-primary btn-sm" type="button" title="Add Test Suite">
                    <i class="bi bi-plus-lg"></i> Test Case
                </button>
            </div>
        </div>

        <div class="test_cases_list">

            <div class="test_case border-bottom py-1 mt-2 d-flex justify-content-between">

                <div class="case_header d-flex justify-content-start pb-2">
                    <div style="width: 140px" class="me-1">
                        <i class="bi bi-chevron-double-up text-danger"></i>
                        <i class="bi bi-robot mx-1"></i>
                        <span class="text-primary">WWW-9999</span>
                    </div>

                    <div class="title">
                        <span>Test case title bla bla </span>
                    </div>
                </div>

                <div class="controls" >
                    <button class="btn py-0 px-2" type="button" title="Edit">
                        {{--                     onclick="renderTestCaseEditForm('{{$testCase->id}}')--}}
                        <i class="bi bi-pencil"></i>
                    </button>

                    <button class="btn py-0 px-2" type="button" title="Delete">
                        {{--                    onclick="deleteTestCase({{$testCase->id}})"--}}
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>


            </div>



            <div class="test_case border-bottom py-1 mt-2">

                <div class="case_header d-flex justify-content-between pb-2">
                    <div>
                        <i class="fs-5 bi bi-chevron-double-up text-danger"></i>
                        <i class="fs-5 bi bi-robot mx-2 "></i>
                        <span class="fs-6 text-primary">WEB-9999</span>
                    </div>

                    <div class="controls">
                        <button class="btn px-2" type="button" title="Edit">
                            {{--                     onclick="renderTestCaseEditForm('{{$testCase->id}}')--}}
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button class="btn px-2" type="button" title="Delete">
                            {{--                    onclick="deleteTestCase({{$testCase->id}})"--}}
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>

                <div class="title">
                    <span>Test case title bla bla bla it really long and boring case it really long and boring case</span>
                </div>
            </div>

            <div class="test_case border-bottom py-1 mt-2">

                <div class="case_header d-flex justify-content-between">
                    <div>
                        <i class="fs-5 bi bi-chevron-double-up text-danger"></i>
                        <i class="fs-5 bi bi-robot mx-2 "></i>
                        <span class="fs-6 text-primary">WEB-9999</span>
                    </div>

                    <div class="controls">
                        <button class="btn p-0 px-1" type="button" title="Edit">
                            {{--                     onclick="renderTestCaseEditForm('{{$testCase->id}}')--}}
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button class="btn p-0 px-1" type="button" title="Delete">
                            {{--                    onclick="deleteTestCase({{$testCase->id}})"--}}
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                </div>

                <div class="title">
                    <span>Test case title bla bla bla it really long and boring case it really long and boring case</span>
                </div>
            </div>

        </div>



    </div>

    <div id="test_case_viewer" class="col">





    </div>

@endsection

{{--<i class=" fs-3 bi bi-chevron-double-up text-danger"></i>--}}
{{--<i class=" fs-3 bi bi-chevron-double-down text-warning"></i>--}}
{{--<i class=" fs-3 bi bi-list text-success"></i>--}}

<div id="test_suite_overlay" class="overlay" style="display: none">

    <div class="card position-absolute top-50 start-50 translate-middle border-secondary" style="width: 500px">
        <form class="px-5 pt-3">

            <h4>
                Create Test Suite
            </h4>
            <hr>

            <input id="repository_id" type="hidden" value="{{$repository->id}}">

            <div class="mb-3">
                <label for="title" class="form-label">Suite name</label>
                <input type="title" class="form-control" id="test_suite_title_input" placeholder="New test suite">
            </div>

            <div class="mb-3">
                <label for="title" class="form-label">Parent suite</label>
            </div>


            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-success mx-3" onclick="createSuite()">Create</button>
                <button type="button" class="btn btn-danger" onclick="closeSuiteForm()">Cancel</button>
            </div>

        </form>
    </div>

</div>

@section('footer')

        <script src="{{asset('repository/tree.js')}}"></script>
        <script src="{{asset('repository/script.js')}}"></script>

{{--    <script src="{{asset('js/repository_test_suite_crud.js')}}"></script>--}}
{{--    <script src="{{asset('js/repository_test_case_crud.js')}}"></script>--}}
{{--    <script src="{{asset('js/repository_test_case_viewer.js')}}"></script>--}}

    <script>

        interact('#suites_tree_col')
            .resizable({
                edges: { top: false, left: false, bottom: false, right: true },
                listeners: {
                    move: function (event) {
                        let { x, y } = event.target.dataset

                        x = (parseFloat(x) || 0) + event.deltaRect.left

                        Object.assign(event.target.style, {
                            width: `${event.rect.width}px`,
                        })

                        Object.assign(event.target.dataset, { x })
                    }
                }
            })

    </script>


@endsection
