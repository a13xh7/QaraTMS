@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    {{--    TEST SUITES TREE COLUMN--}}
    <div class="col shadow-sm test_suites_col">

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

        <div id="tree">
            @include('repository.tree')
        </div>

        <div id="tse_area"> </div>
    </div>


    <div id="test_case_col" class="col-auto resizable px-0">

    </div>


@endsection


@section('footer')
    <script src="{{asset('js/repository_test_suite_crud.js')}}"></script>
    <script src="{{asset('js/repository_test_case_crud.js')}}"></script>
@endsection
