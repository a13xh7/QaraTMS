@extends('layout.base_layout')

@section('title', 'Permission Denied')

@section('content')
<div class="container p-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white d-flex align-items-center">
                    <i class="bi bi-shield-exclamation me-2 fs-4"></i>
                    <h5 class="card-title mb-0">Permission Denied</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ asset('img/access-denied.svg') }}" alt="Permission Denied" style="max-width: 200px;">
                    </div>
                    
                    <div class="alert alert-danger">
                        <p class="mb-0"><strong>Your account:</strong> {{ Auth::user()->email }}</p>
                        <p class="mb-0"><strong>Missing permission:</strong> '{{ session('permission') ?? request('permission') ?? 'required permission' }}'</p>
                    </div>
                    
                    <p>You don't have the necessary permissions to access this page. If you believe this is an error, please contact your system administrator for assistance.</p>
                    
                    <div class="mt-4">
                        <a href="{{ route('project_list_page') }}" class="btn btn-primary">
                            <i class="bi bi-house-door me-1"></i> Return to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('head')
<style>
    .card-header {
        font-size: 1.2rem;
    }
    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }
</style>
@endsection 