@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')


    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                {{ __('ui.create_project') }}
            </h3>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>{{ __('ui.validation_error_title') }}</strong> {{ __('ui.validation_error_message') }}<br><br>
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
                    <label for="title" class="form-label">{{ __('ui.project_name') }}</label>
                    <input type="text" class="form-control" name="title" placeholder="{{ __('ui.project_name_placeholder') }}" required maxlength="100">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('ui.description') }}</label>
                    <textarea class="form-control" name="description" placeholder="{{ __('ui.description_placeholder') }}" maxlength="255"> </textarea>
                </div>

                <button type="submit" class="btn btn-success px-5">
                    {{ __('ui.create') }}
                </button>

                <a href="{{ url()->previous() }}" class="btn btn-outline-dark px-5 ms-2">
                    <b>{{ __('ui.cancel') }}</b>
                </a>
            </form>
        </div>


        @endsection
    </div>

