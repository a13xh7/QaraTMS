@extends('layout.base_layout')

@section('title', 'Settings Access Management')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Settings Access Management</h1>
                <p class="text-muted">Control which users can access and modify system settings</p>
            </div>
            <div>
                <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus-fill me-1"></i> Add New User
                </button>
                <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Settings
                </a>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">User Permissions</h5>
                </div>
                <div class="card-body">
                    <p>This page allows administrators to manage which users have access to view and modify system settings.</p>
                    
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    <form method="POST" action="{{ route('settings.update_settings_access') }}" class="mt-4">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;"></th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="user_ids[]" value="{{ $user->id }}" 
                                                       id="user_{{ $user->id }}" {{ $user->hasPermissionTo('access_settings') ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <label for="user_{{ $user->id }}" class="form-check-label">
                                                {{ $user->name }}
                                            </label>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <select name="user_roles[{{ $user->id }}]" class="form-select form-select-sm">
                                                <option value="Administrator" {{ $user->role == 'Administrator' ? 'selected' : '' }}>Administrator</option>
                                                <option value="CTO" {{ $user->role == 'CTO' ? 'selected' : '' }}>CTO</option>
                                                <option value="Head Of Engineer" {{ $user->role == 'Head Of Engineer' ? 'selected' : '' }}>Head Of Engineer</option>
                                                <option value="EM" {{ $user->role == 'EM' ? 'selected' : '' }}>EM</option>
                                                <option value="EM PC" {{ $user->role == 'EM PC' ? 'selected' : '' }}>EM PC</option>
                                                <option value="EM Apps" {{ $user->role == 'EM Apps' ? 'selected' : '' }}>EM Apps</option>
                                                <option value="TE Manager" {{ $user->role == 'TE Manager' ? 'selected' : '' }}>TE Manager</option>
                                                <option value="Moderator (Test Engineer)" {{ $user->role == 'Moderator (Test Engineer)' ? 'selected' : '' }}>Moderator (Test Engineer)</option>
                                                <option value="Test Engineer" {{ $user->role == 'Test Engineer' ? 'selected' : '' }}>Test Engineer</option>
                                                <option value="User" {{ $user->role == 'User' || $user->role == null ? 'selected' : '' }}>User</option>
                                            </select>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Default Access</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">The following users have default access to settings:</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            TE Manager
                            <span class="badge bg-primary">TE Manager</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">CTO</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                        
                            <span class="badge bg-primary">Head Of Engineer</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">EM</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">EM PC</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">EM Apps</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Help</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Settings access gives users permission to view and modify system configuration, including integrations, dashboard access, and menu visibility.</p>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> Users with settings access can grant or revoke access to other users and modify their roles.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('settings.add_user') }}">
                @csrf
                <div class="modal-body">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                            <option value="Administrator" {{ old('role') == 'Administrator' ? 'selected' : '' }}>Administrator</option>
                            <option value="CTO" {{ old('role') == 'CTO' ? 'selected' : '' }}>CTO</option>
                            <option value="Head Of Engineer" {{ old('role') == 'Head Of Engineer' ? 'selected' : '' }}>Head Of Engineer</option>
                            <option value="EM" {{ old('role') == 'EM' ? 'selected' : '' }}>EM</option>
                            <option value="EM PC" {{ old('role') == 'EM PC' ? 'selected' : '' }}>EM PC</option>
                            <option value="EM Apps" {{ old('role') == 'EM Apps' ? 'selected' : '' }}>EM Apps</option>
                            <option value="TE Manager" {{ old('role') == 'TE Manager' ? 'selected' : '' }}>TE Manager</option>
                            <option value="Moderator (Test Engineer)" {{ old('role') == 'Moderator (Test Engineer)' ? 'selected' : '' }}>Moderator (Test Engineer)</option>
                            <option value="Test Engineer" {{ old('role') == 'Test Engineer' ? 'selected' : '' }}>Test Engineer</option>
                            <option value="User" {{ old('role', 'User') == 'User' ? 'selected' : '' }}>User</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="access_settings" name="access_settings" {{ old('access_settings') ? 'checked' : '' }}>
                        <label class="form-check-label" for="access_settings">Grant Settings Access</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Show add user modal if there are errors
        @if($errors->any())
            const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            addUserModal.show();
        @endif
        
        console.log('Settings access management page loaded');
    });
</script>
@endsection 