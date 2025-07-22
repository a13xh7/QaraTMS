@php use Illuminate\Support\MessageBag;
/**
 * @var MessageBag $errors
 */
@endphp
@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Create user
            </h3>
        </div>

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 list-unstyled">
                    @foreach ($errors->all() as $error)
                        <li><i class="fas fa-exclamation-circle me-2"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{route('user_create')}}" method="POST">
            @csrf
            <div class="row m-0">

                <div class="col p-3 shadow me-3">

                    <div class="form-group mb-3">
                        <input name="name" type="text" placeholder="Name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <input id="email" name="email" type="email" placeholder="Enter email address" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" required>
                            <span class="input-group-text valid-feedback-icon d-none text-success">
                                <i class="fas fa-check"></i>
                            </span>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" name="password" type="password" 
                               placeholder="Minimum 6 characters" 
                               class="form-control @error('password') is-invalid @enderror"
                               value="{{ old('password') }}" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Password must be at least 6 characters long.</div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" 
                               placeholder="Confirm password" 
                               class="form-control @error('password_confirmation') is-invalid @enderror"
                               value="{{ old('password_confirmation') }}" required>
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ url('/users') }}" class="btn btn-outline-secondary px-4 me-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="submitSpinner"></span>
                            <span id="submitText">Create User</span>
                        </button>
                    </div>

                </div>


                <div class="col p-3 shadow">

                    <h4 class="mb-3">User Permissions</h4>
                    
                    <div class="form-group mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                            <option value="">Select a role</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Standard User</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <p class="text-muted mb-3">Or customize individual permissions below. Click on entity names to toggle both permissions at once</p>

                    <hr>

                    <table class="table table-striped">

                        <thead>
                        <tr class="table-primary">
                            <th scope="col">Entity</th>
                            <th scope="col" class="text-center">Add & Edit</th>
                            <th scope="col" class="text-center">Delete</th>
                        </tr>
                        </thead>

                        <tbody>

                        <tr>
                            <th scope="row" class="permission-toggle" tabindex="0" role="button" aria-label="Toggle Project permissions">Project</th>
                            <td class="text-center"><input name="add_edit_projects" class="form-check-input" type="checkbox" {{ old('add_edit_projects') ? 'checked' : '' }} aria-label="Add and edit projects permission"></td>
                            <td class="text-center"><input name="delete_projects" class="form-check-input" type="checkbox" {{ old('delete_projects') ? 'checked' : '' }} aria-label="Delete projects permission"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="permission-toggle" tabindex="0" role="button" aria-label="Toggle Repository permissions">Repository</th>
                            <td class="text-center"><input name="add_edit_repositories" class="form-check-input" type="checkbox" {{ old('add_edit_repositories') ? 'checked' : '' }} aria-label="Add and edit repositories permission"></td>
                            <td class="text-center"><input name="delete_repositories" class="form-check-input" type="checkbox" {{ old('delete_repositories') ? 'checked' : '' }} aria-label="Delete repositories permission"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="permission-toggle" tabindex="0" role="button" aria-label="Toggle Test Suite permissions">Test Suite</th>
                            <td class="text-center"><input name="add_edit_test_suites" class="form-check-input" type="checkbox" {{ old('add_edit_test_suites') ? 'checked' : '' }} aria-label="Add and edit test suites permission"></td>
                            <td class="text-center"><input name="delete_test_suites" class="form-check-input" type="checkbox" {{ old('delete_test_suites') ? 'checked' : '' }} aria-label="Delete test suites permission"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="permission-toggle" tabindex="0" role="button" aria-label="Toggle Test Case permissions">Test Case</th>
                            <td class="text-center"><input name="add_edit_test_cases" class="form-check-input" type="checkbox" {{ old('add_edit_test_cases') ? 'checked' : '' }} aria-label="Add and edit test cases permission"></td>
                            <td class="text-center"><input name="delete_test_cases" class="form-check-input" type="checkbox" {{ old('delete_test_cases') ? 'checked' : '' }} aria-label="Delete test cases permission"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="permission-toggle" tabindex="0" role="button" aria-label="Toggle Test Plan permissions">Test Plan</th>
                            <td class="text-center"><input name="add_edit_test_plans" class="form-check-input" type="checkbox" {{ old('add_edit_test_plans') ? 'checked' : '' }} aria-label="Add and edit test plans permission"></td>
                            <td class="text-center"><input name="delete_test_plans" class="form-check-input" type="checkbox" {{ old('delete_test_plans') ? 'checked' : '' }} aria-label="Delete test plans permission"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="permission-toggle" tabindex="0" role="button" aria-label="Toggle Test Run permissions">Test Run</th>
                            <td class="text-center"><input name="add_edit_test_runs" class="form-check-input" type="checkbox" {{ old('add_edit_test_runs') ? 'checked' : '' }} aria-label="Add and edit test runs permission"></td>
                            <td class="text-center"><input name="delete_test_runs" class="form-check-input" type="checkbox" {{ old('delete_test_runs') ? 'checked' : '' }} aria-label="Delete test runs permission"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="permission-toggle" tabindex="0" role="button" aria-label="Toggle Document permissions">Document</th>
                            <td class="text-center"><input name="add_edit_documents" class="form-check-input" type="checkbox" {{ old('add_edit_documents') ? 'checked' : '' }} aria-label="Add and edit documents permission"></td>
                            <td class="text-center"><input name="delete_documents" class="form-check-input" type="checkbox" {{ old('delete_documents') ? 'checked' : '' }} aria-label="Delete documents permission"></td>
                        </tr>

                        <tr>
                            <th scope="row" class="permission-toggle" tabindex="0" role="button" aria-label="Toggle User permissions">User</th>
                            <td class="text-center" colspan="2"><input name="manage_users" class="form-check-input" type="checkbox" {{ old('manage_users') ? 'checked' : '' }} aria-label="Manage users permission"></td>
                        </tr>

                        </tbody>
                    </table>

                    <div class="text-muted small mt-2">
                        <i class="fas fa-info-circle me-1"></i> Users cannot delete their own account for security reasons.
                    </div>

                </div>

            </div>

        </form>

        <!-- Permission validation popup -->
        <div class="modal fade" id="permissionValidationModal" tabindex="-1" aria-labelledby="permissionValidationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="permissionValidationModalLabel">Permission Required</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="permissionValidationMessage">Please select at least one permission before creating the user.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('footer')
    <script>
        // Toggle permissions when clicking on entity name
        $('body').on('click', 'th.permission-toggle', function () {
            $(this).next().find('input[type=checkbox]').each(function () {
                this.checked = !this.checked;
            });
            $(this).next().next().find('input[type=checkbox]').each(function () {
                this.checked = !this.checked;
            });
        });
        
        // Form validation
        $(document).ready(function() {
            // Role selection handler
            $('#role').change(function() {
                const role = $(this).val();
                
                // Reset all checkboxes first
                $('input[type=checkbox]').prop('checked', false);
                
                if (role === 'admin') {
                    // Administrator: all permissions
                    $('input[type=checkbox]').prop('checked', true);
                } else if (role === 'manager') {
                    // Manager: all permissions except user permissions
                    $('input[type=checkbox]').prop('checked', true);
                    $('input[name="add_edit_users"]').prop('checked', false);
                    $('input[name="delete_users"]').prop('checked', false);
                } else if (role === 'user') {
                    // Standard User: only Add & Edit permissions (not for users)
                    $('td:nth-child(2) input[type=checkbox]').prop('checked', true);
                    $('input[name="add_edit_users"]').prop('checked', false);
                }
            });
            
            $('form').on('submit', function(e) {
                const password = $('#password').val();
                const confirmation = $('#password_confirmation').val();
                
                // Check if at least one permission checkbox is checked
                const hasPermissions = $('input[type=checkbox]:checked').length > 0;
                
                if (!hasPermissions) {
                    e.preventDefault();
                    $('#permissionValidationMessage').text('Please select at least one permission before creating the user');
                    new bootstrap.Modal(document.getElementById('permissionValidationModal')).show();
                    return false;
                }
                
                if (password !== confirmation) {
                    e.preventDefault();
                    $('#permissionValidationMessage').text('Password and confirmation do not match');
                    new bootstrap.Modal(document.getElementById('permissionValidationModal')).show();
                    return false;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    $('#permissionValidationMessage').text('Password must be at least 6 characters long');
                    new bootstrap.Modal(document.getElementById('permissionValidationModal')).show();
                    return false;
                }
                
                $('#submitBtn').attr('disabled', true);
                $('#submitSpinner').removeClass('d-none');
                $('#submitText').text('Creating...');
                
                setTimeout(function() {
                    if ($('#submitBtn').attr('disabled')) {
                        $('#submitBtn').attr('disabled', false);
                        $('#submitSpinner').addClass('d-none');
                        $('#submitText').text('Create User');
                        alert('The request is taking longer than expected. Please try again if the user was not created.');
                    }
                }, 5000);
            });
        });
    </script>
@endsection

<style>
    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    td.text-center {
        vertical-align: middle;
    }
    
    /* Add these new styles for better checkbox centering */
    td.text-center input[type="checkbox"] {
        display: block;
        margin: 0 auto;
    }
    
    /* Ensure table cells have proper padding */
    .table td, .table th {
        padding: 0.75rem;
        vertical-align: middle;
    }
</style>
