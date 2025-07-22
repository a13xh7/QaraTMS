@extends('layout.base_layout')

@section('title', 'Advanced Settings')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Advanced Settings</h1>
                <p class="text-muted">Configure advanced system settings and defaults</p>
            </div>
            <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Settings
            </a>
        </div>
    </div>
    
    <!-- Advanced Settings -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">System Configuration</h5>
                    <span class="badge bg-warning">Advanced</span>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    <form method="POST" action="{{ route('settings.update_advanced') }}">
                        @csrf
                        <div class="alert alert-warning mb-4" role="alert">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading">Warning: Advanced Settings</h5>
                                    <p class="mb-0">Changing these settings can affect system functionality. Make sure you understand the implications before making changes.</p>
                                </div>
                            </div>
                        </div>
                        
                        <h6 class="mb-3">Default Environment Configuration</h6>
                        <p>Apply the default environment settings for all integrations. This will update the following parameters:</p>
                        
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Confluence Base URL</span>
                                <span class="badge bg-secondary">https://admin.atlassian.net/wiki</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Confluence Space Key</span>
                                <span class="badge bg-secondary">admin</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Jira URL</span>
                                <span class="badge bg-secondary">https://admin.atlassian.net/rest/api/2/search</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>GitLab URL</span>
                                <span class="badge bg-secondary">https://gitlab.com/api/v4</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>API Credentials</span>
                                <span class="badge bg-secondary">Set to default values</span>
                            </li>
                        </ul>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Apply Default Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Environment Status</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Current environment configuration status:</p>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Confluence</span>
                        <span class="badge bg-{{ env('CONFLUENCE_BASE_URL') ? 'success' : 'danger' }}">
                            {{ env('CONFLUENCE_BASE_URL') ? 'Configured' : 'Not Configured' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Jira</span>
                        <span class="badge bg-{{ env('JIRA_URL_SEARCH') ? 'success' : 'danger' }}">
                            {{ env('JIRA_URL_SEARCH') ? 'Configured' : 'Not Configured' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>GitLab</span>
                        <span class="badge bg-{{ env('GITLAB_URL') ? 'success' : 'danger' }}">
                            {{ env('GITLAB_URL') ? 'Configured' : 'Not Configured' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Slack</span>
                        <span class="badge bg-{{ env('SLACK_BOT_TOKEN') ? 'success' : 'danger' }}">
                            {{ env('SLACK_BOT_TOKEN') ? 'Configured' : 'Not Configured' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Help</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Use these settings to reset your environment to the default configuration. This is useful if you've made configuration errors or want to start with a clean slate.</p>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> Applying default settings will overwrite your current configuration.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 