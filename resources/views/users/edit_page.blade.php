@php use App\Models\User;use Illuminate\Support\MessageBag;
/**
 * @var User $user
 * @var MessageBag $errors
 */
@endphp
@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">
        <div class="border-bottom mb-4 d-flex justify-content-between align-items-center">
            <h3 class="page_title">
                <i class="fas fa-user-edit me-2"></i> Edit User
            </h3>
            <a href="{{ url('/users') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{route('user_update')}}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{$user->id}}">
            <input type="hidden" name="has_permissions" value="0" id="hasPermissions">

            <div class="row">
                <div class="col-lg-5 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">User Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-floating mb-3">
                                <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" 
                                       placeholder="Name" required value="{{old('name', $user->name)}}">
                                <label for="name">Name</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                                       placeholder="Email" required value="{{old('email', $user->email)}}">
                                <label for="email">Email</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input id="password" name="password" type="password" 
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Password" minlength="6">
                                <label for="password">New Password</label>
                                <div class="form-text">
                                    <small>Minimum 6 characters. Leave empty to keep current password.</small>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ url('/users') }}" class="btn btn-outline-secondary me-2">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">User Permissions</h5>
                                <button type="button" id="toggleAllPermissions" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-check-double me-1"></i> Toggle All
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label for="role" class="form-label fw-bold">Role</label>
                                <select class="form-select @error('role') is-invalid @enderror" name="role" id="role">
                                    <option value="">Select a role</option>
                                    <option value="administrator" @if($user->hasRole('administrator')) selected @endif>Administrator</option>
                                    <option value="manager" @if($user->hasRole('manager')) selected @endif>Manager</option>
                                    <option value="standard_user" @if($user->hasRole('standard_user')) selected @endif>Standard User</option>
                                </select>
                                <div class="form-text">
                                    <small>Selecting a role will automatically set appropriate permissions</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold">Custom Permissions</label>
                                </div>
                                <div class="alert alert-info py-2">
                                    <small><i class="fas fa-info-circle me-1"></i> Click on entity names to toggle both permissions at once</small>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover border">
                                        <thead class="table-light">
                                        <tr>
                                            <th scope="col">Entity</th>
                                            <th scope="col" class="text-center">Add & Edit</th>
                                            <th scope="col" class="text-center">Delete</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        <tr>
                                            <th scope="row" class="entity-name" style="cursor: pointer">Project</th>
                                            <td class="text-center"><input name="add_edit_projects" class="form-check-input" type="checkbox"
                                               @if($user->can('add_edit_projects')) checked @endif></td>
                                            <td class="text-center"><input name="delete_projects" class="form-check-input" type="checkbox"
                                               @if($user->can('delete_projects')) checked @endif></td>
                                        </tr>

                                        <tr>
                                            <th scope="row" class="entity-name" style="cursor: pointer">Repository</th>
                                            <td class="text-center"><input name="add_edit_repositories" class="form-check-input" type="checkbox"
                                               @if($user->can('add_edit_repositories')) checked @endif></td>
                                            <td class="text-center"><input name="delete_repositories" class="form-check-input" type="checkbox"
                                               @if($user->can('delete_repositories')) checked @endif></td>
                                        </tr>

                                        <tr>
                                            <th scope="row" class="entity-name" style="cursor: pointer">Test Suite</th>
                                            <td class="text-center"><input name="add_edit_test_suites" class="form-check-input" type="checkbox"
                                               @if($user->can('add_edit_test_suites')) checked @endif></td>
                                            <td class="text-center"><input name="delete_test_suites" class="form-check-input" type="checkbox"
                                               @if($user->can('delete_test_suites')) checked @endif></td>
                                        </tr>

                                        <tr>
                                            <th scope="row" class="entity-name" style="cursor: pointer">Test Case</th>
                                            <td class="text-center"><input name="add_edit_test_cases" class="form-check-input" type="checkbox"
                                               @if($user->can('add_edit_test_cases')) checked @endif></td>
                                            <td class="text-center"><input name="delete_test_cases" class="form-check-input" type="checkbox"
                                               @if($user->can('delete_test_cases')) checked @endif></td>
                                        </tr>

                                        <tr>
                                            <th scope="row" class="entity-name" style="cursor: pointer">Test Plan</th>
                                            <td class="text-center"><input name="add_edit_test_plans" class="form-check-input" type="checkbox"
                                               @if($user->can('add_edit_test_plans')) checked @endif></td>
                                            <td class="text-center"><input name="delete_test_plans" class="form-check-input" type="checkbox"
                                               @if($user->can('delete_test_plans')) checked @endif></td>
                                        </tr>

                                        <tr>
                                            <th scope="row" class="entity-name" style="cursor: pointer">Test Run</th>
                                            <td class="text-center"><input name="add_edit_test_runs" class="form-check-input" type="checkbox"
                                               @if($user->can('add_edit_test_runs')) checked @endif></td>
                                            <td class="text-center"><input name="delete_test_runs" class="form-check-input" type="checkbox"
                                               @if($user->can('delete_test_runs')) checked @endif></td>
                                        </tr>

                                        <tr>
                                            <th scope="row" class="entity-name" style="cursor: pointer">Document</th>
                                            <td class="text-center"><input name="add_edit_documents" class="form-check-input" type="checkbox"
                                               @if($user->can('add_edit_documents')) checked @endif></td>
                                            <td class="text-center"><input name="delete_documents" class="form-check-input" type="checkbox"
                                               @if($user->can('delete_documents')) checked @endif></td>
                                        </tr>

                                        <tr>
                                            <th scope="row" class="entity-name" style="cursor: pointer">User</th>
                                            <td class="text-center" colspan="2"><input name="manage_users" class="form-check-input" type="checkbox"
                                               @if($user->can('manage_users')) checked @endif></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="alert alert-warning py-2 mt-3">
                                    <small><i class="fas fa-exclamation-triangle me-1"></i> Users cannot delete their own account for security reasons.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

@endsection

@section('footer')
    <script>
        $(document).ready(function() {
            // Toggle permissions when clicking on entity names
            $('body').on('click', '.entity-name', function () {
                $(this).next().find('input[type=checkbox]').each(function () {
                    this.checked = !this.checked;
                });
                $(this).next().next().find('input[type=checkbox]').each(function () {
                    this.checked = !this.checked;
                });
            });
            
            // Toggle all permissions
            $('#toggleAllPermissions').on('click', function() {
                const checkboxes = $('table input[type=checkbox]');
                const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
                checkboxes.prop('checked', !allChecked);
            });
            
            // Handle role selection
            $('#role').on('change', function() {
                const role = $(this).val();
                const allCheckboxes = $('input[type=checkbox]');
                const userCheckbox = $('input[name="manage_users"]');
                const addEditCheckboxes = $('input[name^="add_edit_"]');
                const deleteCheckboxes = $('input[name^="delete_"]');
                
                // Reset all checkboxes
                allCheckboxes.prop('checked', false);
                
                if (role === 'administrator') {
                    // Administrator gets all permissions
                    allCheckboxes.prop('checked', true);
                } else if (role === 'manager') {
                    // Manager gets all permissions except user management
                    allCheckboxes.prop('checked', true);
                    userCheckbox.prop('checked', false);
                } else if (role === 'standard_user') {
                    // Standard user gets only Add & Edit permissions, no user management
                    addEditCheckboxes.prop('checked', true);
                    deleteCheckboxes.prop('checked', false);
                    userCheckbox.prop('checked', false);
                }
            });
            
            // Confirm before leaving page if form has been modified
            let formChanged = false;
            $('form :input').on('change', function() {
                formChanged = true;
            });
            
            $(window).on('beforeunload', function() {
                if (formChanged) {
                    return "You have unsaved changes. Are you sure you want to leave?";
                }
            });
            
            // Add this function to update hidden input
            function updateHasPermissions() {
                const hasPermissions = $('input[type=checkbox]:checked').length > 0;
                $('#hasPermissions').val(hasPermissions ? '1' : '0');
            }

            // Call it on checkbox changes
            $('input[type=checkbox]').on('change', function() {
                updateHasPermissions();
            });

            // Update on role changes
            $('#role').on('change', function() {
                updateHasPermissions();
            });

            // Call it on page load
            updateHasPermissions();

            // Update form submit handler
            $('form').on('submit', function(e) {
                const hasPermissions = $('input[type=checkbox]:checked').length > 0;
                if (!hasPermissions) {
                    e.preventDefault();
                    const errorAlert = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>Please select at least one permission before saving changes
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('.alert-danger').remove();
                    $(this).prepend(errorAlert);
                    $('html, body').animate({
                        scrollTop: $(this).offset().top - 100
                    }, 200);
                    return false;
                }
            });
        });
    </script>
@endsection
