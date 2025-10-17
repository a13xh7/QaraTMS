@php use App\Models\Project;use Illuminate\Support\MessageBag;
/**
 * @var Project $project
 * @var MessageBag $errors
 */
@endphp
@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                {{ __('ui.add_test_repository') }}
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

        <div class="base_block shadow p-4">
            <form method="POST" action="{{route('repository_create')}}">
                @csrf

                <input type="hidden" name="project_id" value="{{$project->id}}">

                <div class="mb-3">
                    <label for="title" class="form-label">{{ __('ui.name') }}</label>
                    <input name="title" type="text" class="form-control" placeholder="{{ __('ui.repository_name_placeholder') }}" required maxlength="100">
                </div>

                <div class="mb-3">
                    <label for="prefix" class="form-label">{{ __('ui.prefix') }} <span
                                class="text-muted">({{ __('ui.max_symbols', ['count' => 3]) }})</span></label>
                    <input type="text" class="form-control" name="prefix"
                           placeholder="{{ __('ui.prefix_placeholder') }}"
                           required maxlength="3"
                           pattern="[^\s]+" title="{{ __('ui.no_whitespace_allowed') }}"
                           style="text-transform:uppercase">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">{{ __('ui.description') }}</label>
                    <textarea name="description" class="form-control" placeholder="{{ __('ui.description_placeholder') }}" maxlength="255"> </textarea>
                </div>

                <button type="submit" class="btn btn-success px-5">
                    {{ __('ui.save') }}
                </button>

                <a href="{{ url()->previous() }}" class="btn btn-outline-dark px-5 ms-2">
                    <b>{{ __('ui.cancel') }}</b>
                </a>
            </form>
        </div>

    </div>

@endsection

