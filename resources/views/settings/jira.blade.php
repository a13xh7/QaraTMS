@extends('layout.base_layout')

@section('title', 'Jira Settings')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Jira Settings</h1>
                <p class="text-muted">Configure your Jira integration for the Manager Dashboard</p>
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
    
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Error:</strong> Please check the form for errors.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Jira Connection Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update_jira') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="jira_enabled" class="form-label">Jira Integration</label>
                            </div>
                            <div class="col-md-9">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="jira_enabled" id="jira_enabled" {{ env('JIRA_ENABLED', false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="jira_enabled">Enable Jira Integration</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="jira_url" class="form-label">Jira URL</label>
                            </div>
                            <div class="col-md-9">
                                <input type="url" class="form-control @error('jira_url') is-invalid @enderror" id="jira_url" name="jira_url" value="{{ old('jira_url', env('JIRA_URL')) }}" placeholder="https://your-domain.atlassian.net">
                                @error('jira_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">The base URL of your Jira instance.</div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="jira_username" class="form-label">Jira Username</label>
                            </div>
                            <div class="col-md-9">
                                <input type="email" class="form-control @error('jira_username') is-invalid @enderror" id="jira_username" name="jira_username" value="{{ old('jira_username', env('JIRA_USERNAME')) }}" placeholder="your-email@example.com">
                                @error('jira_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">The email address associated with your Jira account.</div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="jira_api_token" class="form-label">Jira API Token</label>
                            </div>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="password" class="form-control @error('jira_api_token') is-invalid @enderror" id="jira_api_token" name="jira_api_token" value="{{ old('jira_api_token', env('JIRA_API_TOKEN')) }}" placeholder="••••••••••••••••••••••••">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleJiraToken">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('jira_api_token')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Your Jira API token. <a href="https://support.atlassian.com/atlassian-account/docs/manage-api-tokens-for-your-atlassian-account/" target="_blank">How to generate an API token?</a></div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="jira_default_project" class="form-label">Default Project</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" class="form-control" id="jira_default_project" name="jira_default_project" value="{{ old('jira_default_project', env('JIRA_DEFAULT_PROJECT')) }}" placeholder="PROJECT">
                                <div class="form-text">The default Jira project key (optional).</div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="jira_cache_duration" class="form-label">Cache Duration</label>
                            </div>
                            <div class="col-md-9">
                                <div class="input-group">
                                    <input type="number" class="form-control" id="jira_cache_duration" name="jira_cache_duration" value="{{ old('jira_cache_duration', env('JIRA_CACHE_DURATION', 30)) }}" min="0" max="1440">
                                    <span class="input-group-text">minutes</span>
                                </div>
                                <div class="form-text">How long to cache Jira data (recommended: 30 minutes).</div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-primary" id="testJiraConnection">
                                <i class="bi bi-check2-circle me-1"></i> Test Connection
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">About Jira Integration</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex mb-3">
                        <div class="me-3">
                            <div class="icon-container bg-primary bg-opacity-10 text-primary rounded p-2">
                                <i class="bi bi-kanban fs-4"></i>
                            </div>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">Jira Connection</h6>
                            <p class="text-muted mb-0 small">The Jira integration allows the dashboard to connect to your Jira instance and retrieve data for various reports.</p>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" role="alert">
                        <h6 class="alert-heading fw-bold"><i class="bi bi-info-circle me-2"></i>Jira API Token Required</h6>
                        <p class="mb-0 small">For security reasons, Jira requires an API token instead of your account password. You need to generate this token from your Atlassian account settings.</p>
                    </div>
                    
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body p-3">
                            <h6 class="fw-bold">Features Enabled by Jira Integration:</h6>
                            <ul class="mb-0 ps-3 small">
                                <li>JIRA Lead Time tracking</li>
                                <li>Issue status and resolution analysis</li>
                                <li>Sprint metrics and velocity</li>
                                <li>Bug tracking and management</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('head')
<style>
    .icon-container {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
    }
</style>
@endsection

@section('footer')
<script>
    // Toggle password visibility
    document.getElementById('toggleJiraToken').addEventListener('click', function() {
        const tokenInput = document.getElementById('jira_api_token');
        const icon = this.querySelector('i');
        
        if (tokenInput.type === 'password') {
            tokenInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            tokenInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
    
    // Test connection button
    document.getElementById('testJiraConnection').addEventListener('click', function() {
        const jiraUrl = document.getElementById('jira_url').value;
        const jiraUsername = document.getElementById('jira_username').value;
        const jiraApiToken = document.getElementById('jira_api_token').value;
        
        if (!jiraUrl || !jiraUsername || !jiraApiToken) {
            alert('Please fill in all required fields before testing the connection.');
            return;
        }
        
        // Replace with actual test connection logic
        this.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Testing...';
        this.disabled = true;
        
        // Simulate API call
        setTimeout(() => {
            this.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Connection Successful';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-success');
            
            // Reset after 3 seconds
            setTimeout(() => {
                this.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Test Connection';
                this.classList.remove('btn-success');
                this.classList.add('btn-outline-primary');
                this.disabled = false;
            }, 3000);
        }, 2000);
    });
</script>
@endsection 