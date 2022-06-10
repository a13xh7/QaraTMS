@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')


    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Create Project
            </h3>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="base_block p-4 shadow">
            <form action="{{route('project_create')}}" method="POST" enctype="multipart/form-data">

                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label">Project Name</label>
                    <input type="text" class="form-control" name="title" required maxlength="100">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" name="description" maxlength="255"> </textarea>
                </div>

                <button type="submit" class="btn btn-success px-5">
                    Create
                </button>

                <a href="{{ url()->previous() }}" class="btn btn-outline-dark px-5 ms-2">
                    <b>Cancel</b>
                </a>
            </form>
        </div>


        @endsection
    </div>

