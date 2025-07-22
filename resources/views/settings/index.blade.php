@extends('layout.base_layout')

@section('title', 'Settings')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold mb-0">Settings</h1>
            <p class="text-muted">Configure integrations and manage dashboard access permissions</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="list-group shadow-sm">
                <div class="list-group-item list-group-item-dark">
                    <i class="bi bi-gear-fill me-2"></i> System Settings
                </div>
                <a href="{{ route('settings.jira') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-kanban me-2"></i> Jira Settings</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('settings.gitlab') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-git me-2"></i> GitLab Settings</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('settings.confluence') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-book me-2"></i> Confluence Settings</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('settings.menu_visibility') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-menu-button-wide me-2"></i> Menu Visibility</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('settings.advanced') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-gear-wide-connected me-2"></i> Advanced Settings</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
            </div>
            
            <div class="list-group shadow-sm mt-4">
                <div class="list-group-item list-group-item-dark">
                    <i class="bi bi-shield-lock me-2"></i> Access Control
                </div>
                <a href="{{ route('settings.dashboard_access') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-person-check me-2"></i> Dashboard Access</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('settings.settings_access') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-shield-lock me-2"></i> Settings Access</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
            </div>
            
            <div class="list-group shadow-sm mt-4">
                <div class="list-group-item list-group-item-dark">
                    <i class="bi bi-people-fill me-2"></i> Team Management
                </div>
                <a href="{{ route('settings.squad') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people me-2"></i> Squad Settings</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
                <a href="{{ route('settings.scoring') }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-graph-up me-2"></i> Scoring Settings</span>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
            </div>
        </div>
        
        <div class="col-lg-9 col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">System Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Connected Integrations</h6>
                                    <div class="mt-3">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="integration-icon me-3 bg-primary bg-opacity-10 text-primary rounded p-2">
                                                <i class="bi bi-kanban fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Jira</h6>
                                                <small class="text-muted">{{ env('JIRA_ENABLED', false) ? 'Connected' : 'Not Connected' }}</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="integration-icon me-3 bg-danger bg-opacity-10 text-danger rounded p-2">
                                                <i class="bi bi-git fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">GitLab</h6>
                                                <small class="text-muted">{{ env('GITLAB_TOKEN') ? 'Connected' : 'Not Connected' }}</small>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <div class="integration-icon me-3 bg-info bg-opacity-10 text-info rounded p-2">
                                                <i class="bi bi-book fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">Confluence</h6>
                                                <small class="text-muted">{{ env('CONFLUENCE_URL') ? 'Connected' : 'Not Connected' }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Access Statistics</h6>
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span>Manager Dashboard Access</span>
                                            <span class="badge bg-primary">{{ App\Models\User::permission('access_manager_dashboard')->count() }} Users</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Settings Access</span>
                                            <span class="badge bg-primary">{{ App\Models\User::permission('access_settings')->count() }} Users</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="bi bi-info-circle-fill fs-4"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading">Welcome to System Settings</h5>
                                <p class="mb-0">Configure your external integrations and manage dashboard access from this central location. Use the menu on the left to navigate between different settings sections.</p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="{{ route('settings.update_advanced') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Apply Default Settings
                        </button>
                        <div class="form-text">Apply recommended default settings for all integrations</div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('head')
<style>
    .integration-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection 