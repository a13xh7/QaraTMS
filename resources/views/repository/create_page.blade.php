@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Add Test Repository
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

        <div class="base_block shadow p-4">
            <form  method="POST" action="{{route('repository_create')}}">
                @csrf

                <input type="hidden" name="project_id" value="{{$project->id}}">

                <div class="mb-3">
                    <label for="title" class="form-label">Name</label>
                    <input name="title" type="text" class="form-control" required maxlength="100">
                </div>

                <div class="mb-3">
                    <label for="prefix" class="form-label">Prefix <span class="text-muted">(max 3 symbols)</span></label>
                    <input type="text" class="form-control" name="prefix"
                           required maxlength="3"
                           pattern="[^\s]+" title="please dont use the white space :)"
                           style="text-transform:uppercase" >
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control"  maxlength="255"> </textarea>
                </div>

                <button type="submit" class="btn btn-success px-5">
                    Save
                </button>

                <a href="{{ url()->previous() }}" class="btn btn-outline-dark px-5 ms-2">
                    <b>Cancel</b>
                </a>
            </form>
        </div>

    </div>

@endsection

