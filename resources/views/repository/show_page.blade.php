@php use App\Models\Project;use App\Models\Repository;
/**
 * @var Repository $repository
 * @var Project $project
 *
 */
@endphp

@extends('layout.base_layout')

@section('head')
    <link rel="stylesheet" href="{{asset('css/suites_tree.css')}}">

    <link href="{{asset('editor/summernote-repo.css')}}" rel="stylesheet">
    <script src="{{asset('editor/summernote-lite.min.js')}}"></script>
@endsection

@section('content')

    @include('layout.sidebar_nav')

    {{--    TEST SUITES TREE COLUMN--}}
    <div class="col-2 shadow-sm" id="suites_tree_col">

        {{-- COLUMN header--}}
        <div class="border-bottom mt-2 pb-2 mb-2 d-flex justify-content-between">

            <span class="fs-5">{{$repository->title}}</span>

            <div>
                @can('add_edit_test_suites')
                    <button id="add_root_suite_btn" class="btn btn-primary btn-sm" type="button" title="Add Test Suite"
                            onclick="showSuiteForm('create')">
                        <i class="bi bi-plus-lg"></i> Create Test Suite
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

        {{-- TEST SUITES JS tree--}}
        <ul id="tree">
            <li></li>
        </ul>

    </div>

    {{--    TEST CASES LIST COLUMN--}}

    <div id="test_cases_list_col" class="col-9 shadow-sm">

        {{-- COLUMN header--}}
        <div class="border-bottom mt-2 pb-2 ">
            <div class="d-flex">

                @can('add_edit_test_cases')
                    <button class="btn btn-primary btn-sm me-2" type="button" title="Add Test Case"
                            onclick="renderTestCaseCreateForm()">
                        <i class="bi bi-plus-lg"></i> Create Test Case
                    </button>
                @endcan

                <div>
                    <span class="fs-5 text-muted">Test Suite: </span>
                    <span id="test_cases_list_site_title" class="fs-5">
                        Select Test Suite
                    </span>
                </div>

            </div>
        </div>

        {{-- TEST CASES List --}}
        <div id="test_cases_list">
            {{--  js ajax load --}}
        </div>

    </div>

    {{--    TEST CASE Viewer column--}}

    <div id="test_case_col" class="col shadow-sm">
        <div id="test_case_area"></div>
    </div>

    {{--    CREATE TEST SUITE FORM OVERLAY --}}
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
                    <button id="tsf_update_btn" type="button" class="btn btn-success mx-3" style="display: none"
                            onclick="updateSuite()">Update
                    </button>
                    <button id="tsf_create_btn" type="button" class="btn btn-success mx-3" onclick="createSuite()">
                        Create
                    </button>
                    <button type="button" class="btn btn-danger" onclick="closeSuiteForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>


    {{--    CREATE TEST CASE FORM OVERLAY --}}

    <div id="test_case_overlay"
         class="test_case_overlay_modal modal fade"
         tabindex="-1" style="display: none;" aria-hidden="true"
         data-bs-backdrop="static" data-bs-keyboard="false">

        <div class="modal-dialog modal-xl" id="test_case_overlay_data">

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

    <script>

        function removeEditAndAddButtons() {
            $(".edit_suite_btn").remove();
            $(".add_child_suite_btn").remove();
        }

        function removeDeleteButtons() {
            $(".delete_suite_btn").remove();
        }

        if({{$canEditSuites}} === 0) {
            setTimeout(removeEditAndAddButtons, 1000);
        }

        if({{$canDeleteSuites}} === 0) {
            setTimeout(removeDeleteButtons, 1000);
        }


        {{--const testSuite = @json(request('test_suite'));--}}
        {{--const testCase = @json(request('test_case'));--}}

        {{--document.addEventListener("DOMContentLoaded", function () {--}}
        {{--    if (testSuite) {--}}
        {{--        loadCasesList(testSuite);--}}
        {{--    }--}}

        {{--    if (testCase) {--}}
        {{--        renderTestCaseOverlay(testCase);--}}
        {{--    }--}}
        {{--});--}}

    </script>

@endsection
