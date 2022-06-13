@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
               Create user
            </h3>
        </div>


        <form action="{{route('user_create')}}" method="POST">
            @csrf
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
                        <input name="name"  type="text" placeholder="Name" class="form-control" required autofocus>
                    </div>

                    <div class="form-group mb-3">
                        <input name="email" type="text" placeholder="Email" class="form-control" required autofocus>
                    </div>

                    <div class="form-group mb-3">
                        <input name="password" type="password" placeholder="Password. Min 6 symbols" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-end">

                        <button type="submit" class="btn btn-success px-5 mx-2">Create</button>

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
                        <tr class="table-primary">
                            <th scope="col">Entity</th>
                            <th scope="col">Add & Edit</th>
                            <th scope="col">Delete</th>
                        </tr>
                    </thead>

                    <tbody>

                    <tr>
                        <th scope="row">Project</th>
                        <td> <input name="add_edit_projects" class="form-check-input" type="checkbox"></td>
                        <td> <input name="delete_projects" class="form-check-input" type="checkbox"></td>
                    </tr>

                    <tr>
                        <th scope="row">Repository</th>
                        <td><input name="add_edit_repositories" class="form-check-input" type="checkbox"></td>
                        <td><input name="delete_repositories" class="form-check-input" type="checkbox"></td>
                    </tr>

                    <tr>
                        <th scope="row">Test Suite</th>
                        <td><input name="add_edit_test_suites" class="form-check-input" type="checkbox"></td>
                        <td><input name="delete_test_suites" class="form-check-input" type="checkbox"></td>
                    </tr>

                    <tr>
                        <th scope="row">Test Case</th>
                        <td><input name="add_edit_test_cases" class="form-check-input" type="checkbox"></td>
                        <td><input name="delete_test_cases" class="form-check-input" type="checkbox"></td>
                    </tr>

                    <tr>
                        <th scope="row">Test Plan</th>
                        <td><input name="add_edit_test_plans" class="form-check-input" type="checkbox"></td>
                        <td><input name="delete_test_plans" class="form-check-input" type="checkbox"></td>
                    </tr>

                    <tr>
                        <th scope="row">Test Run</th>
                        <td><input name="add_edit_test_runs" class="form-check-input" type="checkbox"></td>
                        <td><input name="delete_test_runs" class="form-check-input" type="checkbox"></td>
                    </tr>

                    <tr>
                        <th scope="row">Document</th>
                        <td><input name="add_edit_documents" class="form-check-input" type="checkbox"></td>
                        <td><input name="delete_documents" class="form-check-input" type="checkbox"></td>
                    </tr>

                    <tr>
                        <th scope="row">User</th>
                        <td colspan="2" style="padding-left: 20%;"  >
                            <input name="manage_users" class="form-check-input" type="checkbox">
                        </td>
                    </tr>

                    </tbody>
                </table>

            </div>

        </div>

        </form>

    </div>



@endsection

@section('footer')
    <script>
        $('body').on('click', 'th', function () {
            $(this).next().find('input[type=checkbox]').each(function () { this.checked = !this.checked; });
            $(this).next().next().find('input[type=checkbox]').each(function () { this.checked = !this.checked; });
        });
    </script>
@endsection
