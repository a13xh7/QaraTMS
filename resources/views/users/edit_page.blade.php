@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
               Edit user
            </h3>
        </div>


        <form action="{{route('user_update')}}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{$user->id}}">

        <div class="row m-0">

            <div class="col p-3 shadow me-3" >

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <div>
                            @foreach ($errors->all() as $error)
                                <span>{{ $error }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif


                    <div class="form-group mb-3">
                        <input name="name"  type="text" placeholder="Name" class="form-control" required autofocus value="{{$user->name}}">
                    </div>

                    <div class="form-group mb-3">
                        <input name="email" type="text" placeholder="Email" class="form-control" required autofocus value="{{$user->email}}">
                    </div>

                    <div class="form-group mb-3">
                        <input name="password" type="password" placeholder="New Password. Min 6 symbols. Leave field empty if you don't want to change password" minlength="6" class="form-control">
                    </div>

                    <div class="d-flex justify-content-end">

                        <button type="submit" class="btn btn-warning px-5 mx-2">Update</button>

                        <a href="{{ url()->previous() }}" class="btn btn-outline-dark px-5">
                            <b>Cancel</b>
                        </a>
                    </div>


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
                        <td> <input name="add_edit_projects" class="form-check-input" type="checkbox"  @if($user->can('add_edit_projects')) checked @endif> </td>
                        <td> <input id="delete_projects" class="form-check-input" type="checkbox" @if($user->can('delete_projects')) checked @endif> </td>
                    </tr>

                    <tr>
                        <th scope="row">Repository</th>
                        <td><input id="add_edit_repositories" class="form-check-input" type="checkbox" @if($user->can('add_edit_repositories')) checked @endif></td>
                        <td><input id="delete_repositories" class="form-check-input" type="checkbox" @if($user->can('delete_repositories')) checked @endif></td>
                    </tr>

{{--                    <tr>--}}
{{--                        <th scope="row">Test Suite</th>--}}
{{--                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>--}}
{{--                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>--}}
{{--                    </tr>--}}

{{--                    <tr>--}}
{{--                        <th scope="row">Test Case</th>--}}
{{--                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>--}}
{{--                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>--}}
{{--                    </tr>--}}

{{--                    <tr>--}}
{{--                        <th scope="row">Test Plan</th>--}}
{{--                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>--}}
{{--                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>--}}
{{--                    </tr>--}}

{{--                    <tr>--}}
{{--                        <th scope="row">Test Run</th>--}}
{{--                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>--}}
{{--                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>--}}
{{--                    </tr>--}}

{{--                    <tr>--}}
{{--                        <th scope="row">Document</th>--}}
{{--                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>--}}
{{--                        <td><input id="" class="form-check-input" type="checkbox" value=""></td>--}}
{{--                    </tr>--}}

{{--                    <tr>--}}
{{--                        <th scope="row">User</th>--}}
{{--                        <td colspan="2">--}}
{{--                            <input  id="" class="form-check-input" type="checkbox" value="">--}}
{{--                        </td>--}}
{{--                    </tr>--}}

                    </tbody>
                </table>

            </div>

        </div>

        </form>

    </div>



@endsection
