@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Add Test Run
            </h3>
        </div>

        <div class="row m-0">

            <div class="col base_block p-3 shadow" style="margin-right: 10px;" >

                <form action="{{route('test_run_create')}}" method="POST">
                    @csrf
                    <input type="hidden" name="project_id" value="{{$project->id}}">

                    <div class="mb-3">
                        <label for="title" class="form-label">Name</label>
                        <input name="title" type="text" class="form-control" value="Test Run" required maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">Select Test Plan</label>
                        <select name="test_plan_id" class="form-select" required>
                            <option disabled selected value> ----- </option>

                            @foreach($testPlans as $testPlan)
                                <option value="{{$testPlan->id}}">{{$testPlan->title}}</option>
                            @endforeach

                        </select>
                    </div>



                    <button type="submit" class="btn btn-success w-100"><b>Save</b></button>
                </form>

            </div>

        </div>



    </div>



@endsection


