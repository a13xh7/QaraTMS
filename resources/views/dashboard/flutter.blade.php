@extends('layout.base_layout')

@section('head')
    <link href="{{ asset_path('css/dashboard.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="flex-grow-1 main-content">
        <div class="container-fluid px-4 py-4">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="page-title mb-1">Apps Automation Dashboard</h3>
                    <p class="text-muted mb-0">Monitor and analyze Flutter automated tests</p>
                </div>
            </div>

            <!-- Construction Message -->
            <div class="card shadow-sm">
                <div class="card-body construction-container">
                    <div class="construction-icon">
                        <i class="bi bi-phone"></i>
                    </div>
                    <h2 class="construction-title">Flutter Dashboard Coming Soon</h2>
                    <p class="construction-message">
                        We're building a powerful Apps Automation Dashboard to help you monitor and analyze your mobile app tests.
                        This feature is currently under construction and will be available soon.
                    </p>
                    <a href="{{ route('project_list_page') }}" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Return to Analytics Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection 