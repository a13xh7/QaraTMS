@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Users

                @can('manage_users')
                    <a class="mx-3" href="{{route('users_create_page')}}">
                        <button type="button" class="btn btn-sm btn-primary"> <i class="bi bi-plus-lg"></i> Add User</button>
                    </a>
                @endcan
            </h3>
        </div>

        <div class="">
            @foreach($users as $user)

                <div class="m-2 base_block shadow-sm border py-3 px-2 ps-4 d-flex justify-content-between  align-items-center">
                    <div>
                        <b> {{$user->name}} </b> -
                        <a href="mailto:{{$user->email}}">{{$user->email}}</a>
                    </div>

                    @can('manage_users')
                    <div class="d-flex justify-content-start">

                        <a href="{{route('users_edit_page', $user->id)}}" class="btn btn-sm btn-outline-dark">
                            <i class="bi bi-pencil"></i>
                            Edit
                        </a>


                        <form action="{{route('user_delete')}}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{$user->id}}">
                            <button type="submit" class="btn btn-sm btn-outline-danger me-3 ms-2">
                                <i class="bi bi-x-lg"></i>
                                Delete
                            </button>
                        </form>
                    </div>
                    @endcan

                </div>

            @endforeach
        </div>

    </div>



@endsection
