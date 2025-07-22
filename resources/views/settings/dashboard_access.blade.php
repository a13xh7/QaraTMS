@extends('layout.base_layout')

@section('title', 'Dashboard Access Settings')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Dashboard Access Settings</h1>
                <p class="text-muted">Manage which users can access the Manager Dashboard</p>
            </div>
            <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Settings
            </a>
        </div>
    </div>
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Manager Dashboard Access</h5>
                <span class="badge bg-primary">{{ $authorizedUsers->count() }} Users with Access</span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('settings.update_dashboard_access') }}" method="POST">
                @csrf
                
                <div class="alert alert-info mb-4" role="alert">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="bi bi-info-circle-fill fs-5"></i>
                        </div>
                        <div>
                            <p class="mb-0">Select the users who should have access to the Manager Dashboard. Only selected users will be able to view the Manager Dashboard and its reports.</p>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th style="width: 60px;">Access</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th style="width: 100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="user-{{ $user->id }}" 
                                        {{ $user->hasPermissionTo('access_manager_dashboard') ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->hasPermissionTo('access_manager_dashboard'))
                                    <span class="badge bg-success">Active</span>
                                    @else
                                    <span class="badge bg-secondary">No Access</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">Default Authorized Users</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">The following users were initially granted access to the Manager Dashboard:</p>
            
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                @php
                $defaultUsers = [
                    'admin@admin.com'
                ];
                @endphp
                
                @foreach($defaultUsers as $email)
                <div class="col">
                    <div class="card h-100 border-0 bg-light">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    {{ substr($email, 0, 2) }}
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ explode('@', $email)[0] }}</h6>
                                    <small class="text-muted">{{ $email }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@section('head')
<style>
    .avatar {
        width: 40px;
        height: 40px;
        font-weight: 500;
        text-transform: uppercase;
    }
    
    .form-switch .form-check-input {
        width: 2.5em;
        height: 1.25em;
    }
</style>
@endsection 