@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Users
{{--                <a class="mx-3" href="{{route("test_run_create_page", $project->id)}}">--}}
{{--                    <button type="button" class="btn btn-sm btn-primary"> <i class="bi bi-plus-lg"></i> Add User</button>--}}
{{--                </a>--}}
            </h3>
        </div>


        <table class="table">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">View</th>
                <th scope="col">Add & Edit</th>
                <th scope="col">Delete</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <th scope="row">Project</th>
                <td>Mark</td>
                <td>Otto</td>
                <td>@mdo</td>
            </tr>
            <tr>
                <th scope="row">Repository</th>
                <td>Jacob</td>
                <td>Thornton</td>
                <td>@fat</td>
            </tr>

            <tr>
                <th scope="row">Test Suite</th>
                <td>Jacob</td>
                <td>Thornton</td>
                <td>@fat</td>
            </tr>

            <tr>
                <th scope="row">Test Case</th>
                <td>Jacob</td>
                <td>Thornton</td>
                <td>@fat</td>
            </tr>

            <tr>
                <th scope="row">Test Plan</th>
                <td>Jacob</td>
                <td>Thornton</td>
                <td>@fat</td>
            </tr>

            <tr>
                <th scope="row">Test Run</th>
                <td>Jacob</td>
                <td>Thornton</td>
                <td>@fat</td>
            </tr>

            </tbody>
        </table>





    </div>
@endsection
