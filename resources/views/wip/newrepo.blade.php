@extends('layout.base_layout')

@section('head')

    <link rel="stylesheet" href="{{asset('repository/style.css')}}">
    <link rel="stylesheet" href="{{asset('repository/tree.css')}}">

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
{{--    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>--}}
@endsection

@section('content')

{{--    @include('layout.sidebar_nav')--}}

    {{--    TEST SUITES TREE COLUMN--}}
    <div class="col-auto tree_col resizable shadow">

        {{-- COLUMN header--}}
        <div class="border-bottom mt-2 pb-2 mb-2 d-flex justify-content-between">
            <span class="fs-5">
                {{$repository->title}}  <i class="bi bi-arrow-right-short"></i> Test Suites Tree
            </span>

            <div>
                <a href="{{route('repository_edit_page', [$project->id, $repository->id])}}"
                   class="btn btn-sm btn-outline-dark me-1"
                   title="Repository Settings">
                    <i class="bi bi-gear"></i>
                </a>

                <button id="add_test_suite_btn" class="btn btn-primary btn-sm" type="button"
                        title="Add Test Suite"
                        onclick="renderTestSuiteEditor('create', {{$repository->id}})">
                    <i class="bi bi-plus-lg"></i> Test Suite
                </button>

            </div>
        </div>


        <ul id="tree">
{{--            @include('repository.tree')--}}
        </ul>

        <div id="tse_area"> </div>
    </div>


    {{--TEST CASE EDITOR COLUMN--}}
{{--    <div class="col" id="test_case_area">--}}

{{--    </div>--}}
    <div id="test_case_col" class="col px-0">

    </div>


@endsection


@section('footer')
        <script src="{{asset('repository/tree.js')}}"></script>
        <script src="{{asset('repository/script.js')}}"></script>

{{--    <script src="{{asset('js/repository_test_suite_crud.js')}}"></script>--}}
{{--    <script src="{{asset('js/repository_test_case_crud.js')}}"></script>--}}
{{--    <script src="{{asset('js/repository_test_case_viewer.js')}}"></script>--}}

    <script>

        interact('.resizable')
            .resizable({
                edges: { top: false, left: false, bottom: false, right: true },
                listeners: {
                    move: function (event) {
                        let { x, y } = event.target.dataset

                        x = (parseFloat(x) || 0) + event.deltaRect.left
                        // y = (parseFloat(y) || 0) + event.deltaRect.top

                        Object.assign(event.target.style, {
                            width: `${event.rect.width}px`,
                            // height: `${event.rect.height}px`,
                            // transform: `translate(${x}px, ${y}px)`
                        })

                        Object.assign(event.target.dataset, { x })
                    }
                }
            })

    </script>

@endsection
