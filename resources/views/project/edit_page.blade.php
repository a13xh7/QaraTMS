@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

<div class="col">
    <div class="d-flex justify-content-between border-bottom my-3">

        <h3 class="page_title"> Edit Project
            <i class="bi bi-arrow-right-short text-muted"></i>
            {{$project->title}}
        </h3>

        <div>
            @can('delete_projects')
                <form method="POST" action={{route("project_delete")}}>
                    @csrf
                    <input type="hidden" name="id" value="{{$project->id}}">

                    <button type="submit" class="btn btn-sm btn-danger">
                        <i class="bi bi-trash3"></i>
                        Delete
                    </button>
                </form>
            @endcan
        </div>

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

    <div class="base_block shadow p-4">
        <form action="{{route('project_update')}}" method="POST">

            @csrf
            <input type="hidden" name="id" value="{{$project->id}}">

            <div class="mb-3">
                <label for="title" class="form-label">Project Name</label>
                <input type="text" class="form-control" name="title" required value="{{$project->title}}" maxlength="100">
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" maxlength="255"> {{$project->description}} </textarea>
            </div>

            <div>
                <button type="submit" class="btn btn-warning px-5 me-2">
                    <b>Update</b>
                </button>

                <a href="{{ url()->previous() }}" class="btn btn-outline-dark px-5">
                    <b>Cancel</b>
                </a>
            </div>

        </form>
    </div>
</div>



@endsection

