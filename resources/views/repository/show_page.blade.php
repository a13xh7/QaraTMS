@extends('layout.base_layout')

@section('head')
    <link rel="stylesheet" href="{{asset('css/suites_tree.css')}}">

    <link href="{{asset('editor/summernote-repo.css')}}" rel="stylesheet">
    <script src="{{asset('editor/summernote-lite.min.js')}}"></script>
@endsection

@section('content')

    {{--    TEST SUITES TREE COLUMN--}}
    <div class="col-3 shadow-sm" id="suites_tree_col">

        {{-- COLUMN header--}}
        <div class="border-bottom mt-2 pb-2 mb-2 d-flex justify-content-between">
            <span class="fs-5">
                <span class="text-muted">Repository:</span> {{$repository->title}}
            </span>

            <div>
                @can('add_edit_test_suites')
                    <button id="add_root_suite_btn" class="btn btn-primary btn-sm" type="button" title="Add Test Suite"
                            onclick="showSuiteForm('create')">
                        <i class="bi bi-plus-lg"></i> Test Suite
                    </button>
                @endcan

                @can('add_edit_repositories')
                    <a href="{{route('repository_edit_page', [$project->id, $repository->id])}}"
                       class="btn btn-sm btn-outline-dark me-1"
                       title="Repository Settings">
                        <i class="bi bi-gear"></i>
                    </a>
                @endcan

            </div>
        </div>

        {{--        <button type="button" class="btn btn-outline-dark btn-sm w-100">ROOT</button>--}}

        <ul id="tree">
            <li></li>
        </ul>

    </div>


    <div id="test_cases_list_col" class="col-9 shadow-sm">
        {{-- COLUMN header--}}
        <div class="border-bottom mt-2 pb-2 ">

            <div class="d-flex justify-content-between">

                <div>
                    <span class="fs-5 text-muted">Suite: </span>
                    <span id="test_cases_list_site_title" class="fs-5">
                    Select Test Suite
                </span>
                </div>

                @can('add_edit_test_cases')
                    <button class="btn btn-primary btn-sm mx-2" type="button" title="Add Test Case"
                            onclick="loadTestCaseCreateForm()">
                        <i class="bi bi-plus-lg"></i> Test Case
                    </button>
                @endcan
            </div>

        </div>

        <div id="test_cases_list">  {{--  js load --}}  </div>
    </div>

    <div id="test_case_col" class="col-5 shadow-sm">
        <div id="test_case_area"></div>
    </div>


    <div id="test_suite_form_overlay" class="overlay" style="display: none">
        <div class="card position-absolute top-50 start-50 translate-middle border-secondary" style="width: 500px">
            <form class="px-5 pt-3">
                <h4 id="tsf_title">
                    Create Test Suite
                </h4>
                <hr>
                <input id="repository_id" type="hidden" value="{{$repository->id}}">
                <div class="mb-3">
                    <label for="title" class="form-label">Suite name</label>
                    <input type="title" class="form-control" id="test_suite_title_input" placeholder="New test suite">
                </div>
                <div class="d-flex justify-content-end mb-3">
                    <button id="tsf_update_btn" type="button" class="btn btn-success mx-3" style="display: none" onclick="updateSuite()">Update</button>
                    <button id="tsf_create_btn" type="button" class="btn btn-success mx-3" onclick="createSuite()">Create</button>
                    <button type="button" class="btn btn-danger" onclick="closeSuiteForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection


@section('footer')
    <script>
        let repository_id = {{$repository->id}};
    </script>

    <script src="{{asset('/js/repo/repository.js')}}"></script>

    <script>

        $("#test_cases_list").sortable({
            update: function (e, u) {

            }
        });

    </script>

@endsection
