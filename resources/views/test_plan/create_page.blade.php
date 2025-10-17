@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                {{ __('ui.create_test_plan') }}
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

        <div class="row m-0">

            <div class="col-4 p-3 shadow" style="margin-right: 10px;">

                <form action="{{route('test_plan_create')}}" method="POST">

                    @csrf

                    <input type="hidden" name="project_id" value="{{$project->id}}">
                    <input type="hidden" name="data" id="test_plan_data" value="">

                    <div class="mb-3">
                        <label for="title" class="form-label">{{ __('ui.name') }}</label>
                        <input type="text" class="form-control" name="title" value="{{ __('ui.test_plan_default_name', ['date' => date('Y.m.d H:i')]) }}"
                               placeholder="{{ __('ui.test_plan_name_placeholder') }}" required maxlength="100">
                    </div>

                    <div class="mb-3">

                        <label for="test_suite_id" class="form-label">{{ __('ui.test_repository') }}</label>

                        <select name="repository_id" id="plan_repository_select" class="form-select"
                                onchange="renderPlanTree(this)" required>
                            <option disabled selected value> {{ __('ui.select_option') }}</option>

                            @foreach($repositories as $repository)
                                <option value="{{$repository->id}}">
                                    {{$repository->title}}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">{{ __('ui.description') }}</label>
                        <textarea class="form-control" name="description" placeholder="{{ __('ui.description_placeholder') }}" maxlength="255" rows="7"> </textarea>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success px-5">
                            <b>{{ __('ui.save') }}</b>
                        </button>

                        <a href="{{ url()->previous() }}" class="btn btn-outline-dark px-5 ms-2">
                            <b>{{ __('ui.cancel') }}</b>
                        </a>
                    </div>

                </form>

            </div>


            <div class="col p-3 shadow">

                <div class="border-bottom position-static d-flex justify-content-between">
                    <h3>{{ __('ui.select_test_cases') }}</h3>

                    <div>
                        <button href="button" class="btn btn-outline-link" onclick="selectAllTestPlanCases()">
                            <i class="bi bi-check-all"></i> {{ __('ui.select_all') }}
                        </button>

                        <button href="button" class="btn btn-outline-link" onclick="deselectAllTestPlanCases()">
                            <i class="bi bi-x-lg"></i> Deselect All
                        </button>
                    </div>

                </div>

                <div id="tree" style="min-height: 75vh; max-height: 75vh; overflow-y: scroll; margin-top: 10px;">

                </div>

            </div>

        </div>

    </div>

@endsection

@section('footer')

    <script src="{{asset('js/test_plan_page.js')}}"></script>

@endsection
