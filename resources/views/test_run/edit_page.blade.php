@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="d-flex justify-content-between border-bottom my-3">

            <h3 class="page_title">
                 Edit Test Run
                <i class="bi bi-arrow-right-short text-muted"></i>
                {{$testRun->title}}
            </h3>

            <div>
                @can('delete_test_runs')
                    <form method="POST" action="{{route("test_run_delete")}}">
                        @csrf
                        <input type="hidden" name="id" value="{{$testRun->id}}">
                        <input type="hidden" name="project_id" value="{{$testRun->project_id}}">

                        <button type="submit" class="btn btn-sm  btn-danger">
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

        <div class="card p-4">
            <form  method="POST" action="{{route('test_run_update')}}">
                @csrf

                <input type="hidden" name="id" value="{{$testRun->id}}">
                <input type="hidden" name="project_id" value="{{$testRun->project_id}}">

                <div class="mb-3">
                    <label for="title" class="form-label">Name</label>
                    <input type="text" class="form-control" name="title" required maxlength="100" value="{{$testRun->title}}">
                </div>


                <button type="submit" class="btn btn-warning px-5 me-2">
                    <b>Update</b>
                </button>

                <a href=" {{ url()->previous() }}" class="btn btn-outline-dark px-5">
                    <b>Cancel</b>
                </a>
            </form>
        </div>


    </div>



@endsection

