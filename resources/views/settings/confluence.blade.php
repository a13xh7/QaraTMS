@extends('layout.base_layout')

@section('title', 'Confluence Settings')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Confluence Integration Settings</h1>
                <p class="text-muted">Configure Confluence connection for documentation and knowledge base integration</p>
            </div>
            <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Settings
            </a>
        </div>
    </div>
    
    <!-- Configuration Form -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Confluence Connection Details</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('settings.update_confluence') }}">
                        @csrf
                        
                        <!-- Enable/Disable Toggle -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="confluence_enabled" name="confluence_enabled" value="1" checked>
                                <label class="form-check-label fw-bold" for="confluence_enabled">Enable Confluence Integration</label>
                                <div class="text-muted small">When enabled, the system will connect to Confluence to fetch and update documentation</div>
                            </div>
                        </div>
                        
                        <!-- Authentication Type -->
                        <div class="mb-3">
                            <label class="form-label">Authentication Method</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="auth_type" id="auth_token" value="token" checked>
                                    <label class="form-check-label" for="auth_token">
                                        API Token
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="auth_type" id="auth_oauth" value="oauth">
                                    <label class="form-check-label" for="auth_oauth">
                                        OAuth 2.0
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Confluence URL -->
                        <div class="mb-3">
                            <label for="confluence_url" class="form-label">Confluence URL</label>
                            <input type="url" class="form-control" id="confluence_url" name="confluence_url" value="https://company.atlassian.net/wiki" required>
                            <div class="form-text">The URL of your Confluence instance (e.g., https://yourcompany.atlassian.net/wiki)</div>
                        </div>
                        
                        <!-- API Token Authentication Fields -->
                        <div id="tokenAuth">
                            <div class="mb-3">
                                <label for="confluence_email" class="form-label">Confluence Email</label>
                                <input type="email" class="form-control" id="confluence_email" name="confluence_email" value="user@company.com" required>
                                <div class="form-text">The email address associated with your Atlassian account</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confluence_token" class="form-label">API Token</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confluence_token" name="confluence_token" value="XXXXXXXXXXXXXXXXXXXX" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleToken">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Generate an API token from your <a href="https://id.atlassian.com/manage-profile/security/api-tokens" target="_blank">Atlassian Account Settings</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- OAuth Authentication Fields (hidden by default) -->
                        <div id="oauthAuth" class="d-none">
                            <div class="mb-3">
                                <label for="client_id" class="form-label">Client ID</label>
                                <input type="text" class="form-control" id="client_id" name="client_id">
                                <div class="form-text">The Client ID from your Atlassian OAuth application</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="client_secret" class="form-label">Client Secret</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="client_secret" name="client_secret">
                                    <button class="btn btn-outline-secondary" type="button" id="toggleSecret">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">The Client Secret from your Atlassian OAuth application</div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <span>After saving, you'll need to complete the OAuth authorization process.</span>
                            </div>
                        </div>
                        
                        <!-- Space Keys -->
                        <div class="mb-3">
                            <label for="confluence_spaces" class="form-label">Confluence Space Keys</label>
                            <input type="text" class="form-control" id="confluence_spaces" name="confluence_spaces" value="PROJECT,DOCS,KB">
                            <div class="form-text">Comma-separated list of Confluence Space keys to integrate with</div>
                        </div>
                        
                        <!-- Default Space -->
                        <div class="mb-3">
                            <label for="default_space" class="form-label">Default Space</label>
                            <select class="form-select" id="default_space" name="default_space">
                                <option value="PROJECT" selected>PROJECT</option>
                                <option value="DOCS">DOCS</option>
                                <option value="KB">KB</option>
                            </select>
                            <div class="form-text">Default Confluence Space to use for new documents</div>
                        </div>
                        
                        <!-- Cache Duration -->
                        <div class="mb-3">
                            <label for="confluence_cache" class="form-label">Cache Duration (minutes)</label>
                            <input type="number" class="form-control" id="confluence_cache" name="confluence_cache" value="60" min="5" max="1440">
                            <div class="form-text">How long to cache Confluence API results (recommended: 30-60 minutes)</div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="button" class="btn btn-secondary" id="testConnection">
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
        
        <!-- Info Panel -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">About Confluence Integration</h5>
                </div>
                <div class="card-body">
                    <p>Connecting to Confluence enables the following features:</p>
                    <ul class="mb-0">
                        <li>Post-Mortem template generation</li>
                        <li>Automated documentation updates</li>
                        <li>Knowledge base search integration</li>
                        <li>Deployment documentation linking</li>
                        <li>Project documentation synchronization</li>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Getting an API Token</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Log in to your Atlassian account</li>
                        <li>Go to <a href="https://id.atlassian.com/manage-profile/security/api-tokens" target="_blank">Atlassian Account Settings</a></li>
                        <li>Click "Create API token"</li>
                        <li>Enter a label (e.g., "QA RATMS Integration")</li>
                        <li>Copy the generated token</li>
                        <li>Paste it into the form on this page</li>
                    </ol>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Connection Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-success rounded-circle me-2" style="width: 14px; height: 14px;"></div>
                        <span class="fw-bold">Connected</span>
                    </div>
                    <div class="text-muted small">
                        <div class="mb-1">Last checked: <strong>12 minutes ago</strong></div>
                        <div class="mb-1">API Rate Limit: <strong>450/500</strong></div>
                        <div>Integrated spaces: <strong>3</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle visibility of Confluence token
        const tokenField = document.getElementById('confluence_token');
        const toggleBtn = document.getElementById('toggleToken');
        
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const type = tokenField.getAttribute('type') === 'password' ? 'text' : 'password';
                tokenField.setAttribute('type', type);
                
                // Toggle icon
                const icon = toggleBtn.querySelector('i');
                if (type === 'text') {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        }
        
        // Toggle visibility of OAuth client secret
        const secretField = document.getElementById('client_secret');
        const toggleSecretBtn = document.getElementById('toggleSecret');
        
        if (toggleSecretBtn) {
            toggleSecretBtn.addEventListener('click', function() {
                const type = secretField.getAttribute('type') === 'password' ? 'text' : 'password';
                secretField.setAttribute('type', type);
                
                // Toggle icon
                const icon = toggleSecretBtn.querySelector('i');
                if (type === 'text') {
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        }
        
        // Toggle between auth methods
        const tokenAuthSection = document.getElementById('tokenAuth');
        const oauthAuthSection = document.getElementById('oauthAuth');
        const authTokenRadio = document.getElementById('auth_token');
        const authOauthRadio = document.getElementById('auth_oauth');
        
        authTokenRadio.addEventListener('change', function() {
            if (this.checked) {
                tokenAuthSection.classList.remove('d-none');
                oauthAuthSection.classList.add('d-none');
            }
        });
        
        authOauthRadio.addEventListener('change', function() {
            if (this.checked) {
                tokenAuthSection.classList.add('d-none');
                oauthAuthSection.classList.remove('d-none');
            }
        });
        
        // Test Confluence connection
        const testBtn = document.getElementById('testConnection');
        
        testBtn.addEventListener('click', function() {
            const url = document.getElementById('confluence_url').value;
            const isToken = document.getElementById('auth_token').checked;
            
            let valid = true;
            
            if (!url) {
                valid = false;
            }
            
            if (isToken) {
                const email = document.getElementById('confluence_email').value;
                const token = document.getElementById('confluence_token').value;
                if (!email || !token) {
                    valid = false;
                }
            } else {
                const clientId = document.getElementById('client_id').value;
                const clientSecret = document.getElementById('client_secret').value;
                if (!clientId || !clientSecret) {
                    valid = false;
                }
            }
            
            if (!valid) {
                alert('Please fill in all required fields before testing');
                return;
            }
            
            // Show testing state
            const originalHtml = testBtn.innerHTML;
            testBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Testing...';
            testBtn.disabled = true;
            
            // Simulate API call
            setTimeout(function() {
                // Show success state (for demo purposes)
                testBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Connection Successful';
                testBtn.classList.remove('btn-secondary');
                testBtn.classList.add('btn-success');
                
                // Reset after 3 seconds
                setTimeout(function() {
                    testBtn.innerHTML = originalHtml;
                    testBtn.classList.remove('btn-success');
                    testBtn.classList.add('btn-secondary');
                    testBtn.disabled = false;
                }, 3000);
            }, 1500);
        });
    });
</script>
@endsection 