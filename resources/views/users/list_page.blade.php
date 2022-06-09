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

        @foreach($users as $user)

            <div>{{$user->email}}</div>
        @endforeach

        <div class="row m-0">

            <div class="col p-3 shadow me-1" >

                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="form-group mb-3">
                        <input type="text" placeholder="Name" id="name" class="form-control" name="name"
                               required autofocus>
                    </div>
                    <div class="form-group mb-3">
                        <input type="text" placeholder="Email" id="email_address" class="form-control"
                               name="email" required autofocus>
                    </div>
                    <div class="form-group mb-3">
                        <input type="password" placeholder="Password" id="password" class="form-control"
                               name="password" required>
                    </div>
                    <div class="form-group mb-3">
                        <div class="checkbox">
                            <label><input type="checkbox" name="remember"> Remember Me</label>
                        </div>
                    </div>
                    <div class="d-grid mx-auto">
                        <button type="submit" class="btn btn-success">Create</button>
                    </div>
                </form>

            </div>


            <div class="col p-3 shadow" >

                <h3>Permissions</h3>
                <hr>

                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th scope="col">Entity</th>
                        <th scope="col">Add & Edit</th>
                        <th scope="col">Delete</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th scope="row">Project</th>
                        <td> <input id="" class="form-check-input" type="checkbox" value=""></td>
                        <td> <input id="" class="form-check-input" type="checkbox" value=""></td>
                    </tr>
                    <tr>
                        <th scope="row">Repository</th>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                    </tr>

                    <tr>
                        <th scope="row">Test Suite</th>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                    </tr>

                    <tr>
                        <th scope="row">Test Case</th>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                    </tr>

                    <tr>
                        <th scope="row">Test Plan</th>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                    </tr>

                    <tr>
                        <th scope="row">Test Run</th>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                    </tr>

                    <tr>
                        <th scope="row">Document</th>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>
                    </tr>

                    <tr>
                        <th scope="row">User</th>
                        <td colspan="2">
                            <input  id="" class="form-check-input" type="checkbox" value="">
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>

        </div>

    </div>



@endsection
