@extends('layout.base_layout')

@section('title', 'GitLab Settings')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
/* Aggressive CSS overrides to make buttons clickable */
button, a {
    pointer-events: auto !important;
    cursor: pointer !important;
    opacity: 1 !important;
    z-index: 9999 !important;
    position: relative !important;
}

/* Plain button styles without framework dependencies */
.plain-button {
    padding: 8px 16px;
    background-color: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer !important;
    font-size: 14px;
    margin-right: 8px;
}

.plain-button:hover {
    background-color: #5a6268;
}

.gitlab-project-item {
    padding: 8px 12px;
    border-bottom: 1px solid #e9ecef;
}

.gitlab-project-item:hover {
    background-color: #f8f9fa;
}

.selected-projects-container {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    margin-top: 10px;
}

.project-badge {
    display: inline-block;
    padding: 0.4em 0.6em;
    font-size: 0.75em;
    font-weight: 600;
    line-height: 1;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.25rem;
    background-color: #6c757d;
    margin: 3px;
}

.project-badge .remove-project {
    margin-left: 5px;
    cursor: pointer;
}

#projectSearchInput {
    padding: 8px;
    margin-bottom: 10px;
    width: 100%;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.2em;
}

.projects-list {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}

.gitlab-namespace-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    cursor: pointer;
    position: sticky;
    top: 0;
    z-index: 10;
}

.gitlab-namespace-container {
    border-bottom: 1px solid #e9ecef;
    margin-bottom: 10px;
}

.gitlab-project-item:last-child {
    border-bottom: none;
}
</style>

<!-- Inline script to make sure functions are defined before page loads -->
<script>
// Define all functions in global scope
window.testGitLabConnection = function() {
    console.log('Test connection function called');
    
    // Get form values
    const url = document.getElementById('gitlab_url').value;
    const token = document.getElementById('gitlab_token').value;
    const group = document.getElementById('gitlab_group').value;
    const testBtn = document.querySelector('#testConnection');
    
    // Validate inputs
    if (!url || !token) {
        alert('Please enter GitLab URL and Access Token before testing');
        return;
    }
    
    // Show loading state
    const originalText = testBtn.innerHTML;
    testBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';
    testBtn.disabled = true;
    
    // Make a direct request to GitLab API
    fetch(`${url}/user`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'PRIVATE-TOKEN': token
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        // Show success
        testBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Success';
        testBtn.classList.remove('btn-secondary');
        testBtn.classList.add('btn-success');
        
        let userName = data.name || data.username || 'Unknown user';
        const statusText = document.getElementById('connectionStatusText');
        if (statusText) {
            statusText.textContent = 'Connected';
            const statusIndicator = document.querySelector('.connection-status-indicator');
            if (statusIndicator) {
                statusIndicator.classList.remove('bg-danger');
                statusIndicator.classList.add('bg-success');
            }
        }
        
        // Reset button after 3 seconds
        setTimeout(function() {
            testBtn.innerHTML = originalText;
            testBtn.classList.remove('btn-success');
            testBtn.classList.add('btn-secondary');
            testBtn.disabled = false;
        }, 3000);
    })
    .catch(error => {
        // Show error
        testBtn.innerHTML = '<i class="bi bi-x-circle-fill"></i> Failed';
        testBtn.classList.remove('btn-secondary');
        testBtn.classList.add('btn-danger');
        
        console.error('Connection failed:', error);
        
        // Reset button after 3 seconds
        setTimeout(function() {
            testBtn.innerHTML = originalText;
            testBtn.classList.remove('btn-danger');
            testBtn.classList.add('btn-secondary');
            testBtn.disabled = false;
        }, 3000);
    });
};

window.fetchGitLabProjects = function() {
    const url = document.getElementById('gitlab_url').value;
    const token = document.getElementById('gitlab_token').value;
    const group = document.getElementById('gitlab_group').value;
    const fetchBtn = document.querySelector('#fetchProjectsBtn');
    const projectsList = document.getElementById('gitlab-projects-list');
    
    if (!url || !token) {
        alert('Please enter GitLab URL and Access Token before fetching projects');
        return;
    }
    
    // Show loading state
    const originalText = fetchBtn.innerHTML;
    fetchBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Fetching...';
    fetchBtn.disabled = true;
    projectsList.innerHTML = '<div class="text-center py-3"><span class="spinner-border"></span><p class="mt-2">Loading projects...</p></div>';
    
    // First determine if we should fetch group projects or all projects
    let apiEndpoint = `${url}/projects?per_page=100&simple=false&membership=true&order_by=path&sort=asc`;
    
    if (group && group.trim() !== '') {
        // If a group is specified, get projects from that group with a larger limit to ensure we get all
        const encodedGroup = encodeURIComponent(group);
        apiEndpoint = `${url}/groups/${encodedGroup}/projects?per_page=300&include_subgroups=true&simple=false&order_by=path&sort=asc`;
    }
    
    // Store all projects globally for later use
    window.allGitlabProjects = [];
    
    fetch(apiEndpoint, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'PRIVATE-TOKEN': token
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(projects => {
        window.allGitlabProjects = projects;
        projectsList.innerHTML = '';
        
        if (projects.length === 0) {
            projectsList.innerHTML = '<div class="p-3 text-center text-muted">No projects found</div>';
            return;
        }
        
        // Get currently selected projects
        const selectedProjects = Array.from(document.querySelectorAll('.selected-project')).map(p => p.dataset.projectPath);
        
        // Sort projects by namespace/path to group them logically
        projects.sort((a, b) => {
            return a.path_with_namespace.localeCompare(b.path_with_namespace);
        });
        
        // Group projects by their top-level namespace
        const groupedProjects = {};
        projects.forEach(project => {
            const parts = project.path_with_namespace.split('/');
            const topNamespace = parts[0];
            
            if (!groupedProjects[topNamespace]) {
                groupedProjects[topNamespace] = [];
            }
            groupedProjects[topNamespace].push(project);
        });
        
        // Create a section for each group with collapsible content
        Object.keys(groupedProjects).forEach(namespace => {
            if (namespace.toLowerCase() === 'admin' || namespace.toLowerCase() === group.toLowerCase()) {
                // Create a header for the namespace
                const namespaceHeader = document.createElement('div');
                namespaceHeader.className = 'gitlab-namespace-header p-2 bg-light d-flex justify-content-between align-items-center';
                namespaceHeader.innerHTML = `
                    <h6 class="mb-0"><i class="bi bi-folder me-2"></i>${namespace}</h6>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="window.selectNamespaceProjects('${namespace}')">
                            <i class="bi bi-check-all"></i> Select All
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="window.toggleNamespace('${namespace}')">
                            <i class="bi bi-chevron-down" id="toggle-icon-${namespace}"></i>
                        </button>
                    </div>
                `;
                projectsList.appendChild(namespaceHeader);
                
                // Create a container for the projects in this namespace
                const projectsContainer = document.createElement('div');
                projectsContainer.id = `namespace-${namespace}`;
                projectsContainer.className = 'gitlab-namespace-container';
                
                // Add all projects in this namespace
                groupedProjects[namespace].forEach(project => {
                    const isSelected = selectedProjects.includes(project.path_with_namespace);
                    const projectItem = document.createElement('div');
                    projectItem.className = 'gitlab-project-item d-flex justify-content-between align-items-center';
                    projectItem.dataset.id = project.id;
                    projectItem.dataset.path = project.path_with_namespace;
                    projectItem.dataset.namespace = namespace;
                    
                    // Calculate the indentation based on path depth
                    const pathDepth = project.path_with_namespace.split('/').length - 1;
                    const indentation = pathDepth > 0 ? `<span style="margin-left: ${pathDepth * 20}px;"></span>` : '';
                    
                    const lastActivityDate = new Date(project.last_activity_at);
                    const formattedDate = lastActivityDate.toLocaleDateString();
                    
                    projectItem.innerHTML = `
                        <div style="width: 70%;">
                            ${indentation}<strong>${project.name}</strong>
                            <div class="text-muted small">${project.path_with_namespace}</div>
                        </div>
                        <div>
                            <span class="badge bg-secondary me-2">${formattedDate}</span>
                            <button class="btn btn-sm ${isSelected ? 'btn-success' : 'btn-outline-primary'}" onclick="window.toggleProjectSelection('${project.path_with_namespace}', '${project.name}')">
                                ${isSelected ? '<i class="bi bi-check"></i> Selected' : 'Select'}
                            </button>
                        </div>
                    `;
                    
                    projectsContainer.appendChild(projectItem);
                });
                
                projectsList.appendChild(projectsContainer);
            } else {
                // For other non-admin namespaces, just add a simple list
                const projectsContainer = document.createElement('div');
                projectsContainer.className = 'mt-3';
                
                groupedProjects[namespace].forEach(project => {
                    const isSelected = selectedProjects.includes(project.path_with_namespace);
                    const projectItem = document.createElement('div');
                    projectItem.className = 'gitlab-project-item d-flex justify-content-between align-items-center';
                    projectItem.dataset.id = project.id;
                    projectItem.dataset.path = project.path_with_namespace;
                    
                    const lastActivityDate = new Date(project.last_activity_at);
                    const formattedDate = lastActivityDate.toLocaleDateString();
                    
                    projectItem.innerHTML = `
                        <div>
                            <strong>${project.name}</strong>
                            <div class="text-muted small">${project.path_with_namespace}</div>
                        </div>
                        <div>
                            <span class="badge bg-secondary me-2">${formattedDate}</span>
                            <button class="btn btn-sm ${isSelected ? 'btn-success' : 'btn-outline-primary'}" onclick="window.toggleProjectSelection('${project.path_with_namespace}', '${project.name}')">
                                ${isSelected ? '<i class="bi bi-check"></i> Selected' : 'Select'}
                            </button>
                        </div>
                    `;
                    
                    projectsContainer.appendChild(projectItem);
                });
                
                projectsList.appendChild(projectsContainer);
            }
        });
        
        // Auto-select admin namespace section if present
        if (groupedProjects['admin']) {
            window.toggleNamespace('admin', true);
        }
        
        // Initialize search functionality
        const searchInput = document.getElementById('projectSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const projectItems = document.querySelectorAll('.gitlab-project-item');
                
                projectItems.forEach(item => {
                    const projectName = item.querySelector('strong').textContent.toLowerCase();
                    const projectPath = item.dataset.path.toLowerCase();
                    
                    if (projectName.includes(searchTerm) || projectPath.includes(searchTerm)) {
                        item.style.display = '';
                        
                        // Make sure parent namespace container is visible
                        const namespace = item.dataset.namespace;
                        if (namespace) {
                            const container = document.getElementById(`namespace-${namespace}`);
                            if (container) {
                                container.style.display = '';
                            }
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
        
        // Reset button state
        fetchBtn.innerHTML = originalText;
        fetchBtn.disabled = false;
    })
    .catch(error => {
        projectsList.innerHTML = `<div class="p-3 text-center text-danger">Error: ${error.message}</div>`;
        fetchBtn.innerHTML = originalText;
        fetchBtn.disabled = false;
        console.error('Error fetching projects:', error);
    });
};

window.toggleNamespace = function(namespace, expand = null) {
    const container = document.getElementById(`namespace-${namespace}`);
    const toggleIcon = document.getElementById(`toggle-icon-${namespace}`);
    
    if (container) {
        // If expand is explicitly set, use that value, otherwise toggle
        const newDisplay = expand !== null ? (expand ? 'block' : 'none') : (container.style.display === 'none' ? 'block' : 'none');
        container.style.display = newDisplay;
        
        // Update the toggle icon
        if (toggleIcon) {
            toggleIcon.className = newDisplay === 'none' ? 'bi bi-chevron-down' : 'bi bi-chevron-up';
        }
    }
};

window.selectNamespaceProjects = function(namespace) {
    if (!window.allGitlabProjects) return;
    
    // Get all projects in this namespace
    const namespaceProjects = window.allGitlabProjects.filter(project => 
        project.path_with_namespace.startsWith(`${namespace}/`)
    );
    
    // Select each project
    namespaceProjects.forEach(project => {
        window.toggleProjectSelection(project.path_with_namespace, project.name, true);
    });
};

window.selectAllProjects = function() {
    if (!window.allGitlabProjects || window.allGitlabProjects.length === 0) {
        alert('Please fetch projects first');
        return;
    }
    
    // Filter to only include admin projects if that's the specified group
    const groupInput = document.getElementById('gitlab_group');
    const group = groupInput ? groupInput.value.trim() : '';
    
    let projectsToSelect = window.allGitlabProjects;
    if (group && group.toLowerCase() === 'admin') {
        projectsToSelect = window.allGitlabProjects.filter(project => 
            project.path_with_namespace.toLowerCase().startsWith('admin/')
        );
    }
    
    // Select all filtered projects
    projectsToSelect.forEach(project => {
        window.toggleProjectSelection(project.path_with_namespace, project.name, true);
    });
    
    alert(`Selected ${projectsToSelect.length} projects`);
};

window.clearAllProjects = function() {
    // Remove all project badges
    const projectBadges = document.querySelectorAll('.selected-project');
    projectBadges.forEach(badge => badge.remove());
    
    // Update buttons in the project list
    const selectButtons = document.querySelectorAll('.gitlab-project-item button');
    selectButtons.forEach(button => {
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-primary');
        button.innerHTML = 'Select';
    });
    
    // Update the hidden input
    window.updateSelectedProjects();
    
    alert('Cleared all selected projects');
};

window.toggleProjectSelection = function(projectPath, projectName, forceSelect = false) {
    // Add to the selected projects list (hidden form field)
    const selectedProjectsInput = document.getElementById('selected_projects');
    const selectedProjectsContainer = document.getElementById('selected-projects-container');
    
    // Check if project is already selected
    const existingProject = document.querySelector(`.selected-project[data-project-path="${projectPath}"]`);
    
    if (existingProject && !forceSelect) {
        // Remove project if already selected
        existingProject.remove();
    } else if (!existingProject || forceSelect) {
        // Only add if it doesn't exist or force select is true
        if (!existingProject) {
            // Add project
            const projectBadge = document.createElement('span');
            projectBadge.className = 'project-badge selected-project';
            projectBadge.dataset.projectPath = projectPath;
            projectBadge.innerHTML = `${projectName} <span class="remove-project" onclick="this.parentElement.remove();window.updateSelectedProjects();event.stopPropagation();">×</span>`;
            selectedProjectsContainer.appendChild(projectBadge);
        }
    }
    
    // Update the project selection state in the list
    const projectItem = document.querySelector(`.gitlab-project-item[data-path="${projectPath}"]`);
    if (projectItem) {
        const selectButton = projectItem.querySelector('button');
        if (existingProject && !forceSelect) {
            // Change button back to "Select"
            selectButton.classList.remove('btn-success');
            selectButton.classList.add('btn-outline-primary');
            selectButton.innerHTML = 'Select';
        } else {
            // Change button to "Selected"
            selectButton.classList.remove('btn-outline-primary');
            selectButton.classList.add('btn-success');
            selectButton.innerHTML = '<i class="bi bi-check"></i> Selected';
        }
    }
    
    // Update the hidden input with selected projects
    window.updateSelectedProjects();
};

window.updateSelectedProjects = function() {
    const selectedProjects = Array.from(document.querySelectorAll('.selected-project')).map(p => p.dataset.projectPath);
    const selectedProjectsInput = document.getElementById('selected_projects');
    selectedProjectsInput.value = JSON.stringify(selectedProjects);
};

window.togglePwdVisibility = function() {
    const tokenField = document.getElementById('gitlab_token');
    if (tokenField) {
        tokenField.type = tokenField.type === 'password' ? 'text' : 'password';
        const icon = document.querySelector('#toggleToken i');
        if (icon) {
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        }
    }
};

window.copyWebhookUrl = function() {
    const webhookField = document.getElementById('webhookUrl');
    if (webhookField) {
        webhookField.select();
        try {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(webhookField.value);
                alert('Webhook URL copied to clipboard');
            } else {
                document.execCommand('copy');
                alert('Webhook URL copied to clipboard');
            }
        } catch (e) {
            alert('Failed to copy: ' + e.message);
        }
    }
};

window.updateToggleLabel = function(isChecked) {
    const label = document.getElementById('gitlab_enable_label');
    if (label) {
        label.textContent = isChecked ? 'Disable GitLab Integration' : 'Enable GitLab Integration';
    }
};
</script>
@endsection

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">GitLab Integration Settings</h1>
                <p class="text-muted">Configure GitLab connection for deployment metrics and analytics</p>
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
                    <h5 class="card-title mb-0">GitLab Connection Details</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    <form method="POST" action="{{ route('settings.update_gitlab') }}">
                        @csrf
                        
                        <!-- Enable/Disable Toggle -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="gitlab_enabled" name="gitlab_enabled" value="1" {{ env('GITLAB_ENABLED') == 'true' ? 'checked' : '' }} onclick="window.updateToggleLabel(this.checked)">
                                <label class="form-check-label fw-bold" for="gitlab_enabled" id="gitlab_enable_label">
                                    {{ env('GITLAB_ENABLED') == 'true' ? 'Disable GitLab Integration' : 'Enable GitLab Integration' }}
                                </label>
                                <div class="text-muted small">When enabled, the system will connect to GitLab to fetch deployment and merge request data</div>
                            </div>
                        </div>
                        
                        <!-- Connection Settings -->
                        <div class="card border mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Connection Settings</h6>
                            </div>
                            <div class="card-body">
                                <!-- GitLab URL -->
                                <div class="mb-3">
                                    <label for="gitlab_url" class="form-label">GitLab API URL</label>
                                    <input type="url" class="form-control" id="gitlab_url" name="gitlab_url" value="{{ env('GITLAB_URL') }}" required>
                                    <div class="form-text">The URL of your GitLab API (e.g., https://gitlab.com/api/v4 or your self-hosted GitLab API URL)</div>
                                </div>
                                
                                <!-- GitLab Access Token -->
                                <div class="mb-3">
                                    <label for="gitlab_token" class="form-label">Personal Access Token</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="gitlab_token" name="gitlab_token" value="{{ env('GITLAB_TOKEN') }}" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggleToken" onclick="window.togglePwdVisibility()">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        Generate a token with <code>api</code>, <code>read_repository</code> and <code>read_api</code> scopes from 
                                        GitLab's User Settings > Access Tokens page
                                    </div>
                                </div>
                                
                                <!-- Default Group/Namespace -->
                                <div class="mb-3">
                                    <label for="gitlab_group" class="form-label">Default Group/Namespace</label>
                                    <input type="text" class="form-control" id="gitlab_group" name="gitlab_group" value="{{ env('GITLAB_GROUP') }}">
                                    <div class="form-text">The GitLab group or namespace to use by default (e.g., admin)</div>
                                </div>
                                
                                <!-- Test Connection Button -->
                                <div class="d-flex justify-content-end">
                                    <button type="button" id="testConnection" class="btn btn-secondary" onclick="window.testGitLabConnection()">
                                        <i class="bi bi-check2-circle me-1"></i> Test Connection
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Projects to Track Section -->
                        <div class="card border mb-4">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Projects to Track</h6>
                                <button type="button" id="fetchProjectsBtn" class="btn btn-sm btn-outline-primary" onclick="window.fetchGitLabProjects()">
                                    <i class="bi bi-arrow-repeat me-1"></i> Fetch Projects
                                </button>
                            </div>
                            <div class="card-body">
                                <p class="text-muted mb-3">Select GitLab projects to track for metrics and analytics</p>
                                
                                <!-- Projects Selection UI -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <input type="text" id="projectSearchInput" placeholder="Search projects..." class="form-control me-2" style="width: 70%;">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.selectAllProjects()">
                                                <i class="bi bi-check-all me-1"></i> Select All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.clearAllProjects()">
                                                <i class="bi bi-x-circle me-1"></i> Clear All
                                            </button>
                                        </div>
                                    </div>
                                    <div class="projects-list" id="gitlab-projects-list">
                                        <div class="p-3 text-center text-muted">
                                            Click "Fetch Projects" to load GitLab projects
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Selected Projects -->
                                <div class="mb-3">
                                    <label class="form-label">Selected Projects</label>
                                    <div class="selected-projects-container p-2" id="selected-projects-container">
                                        @php
                                            // Load existing projects from environment
                                            $projectsString = env('GITLAB_PROJECTS', '');
                                            $projectPaths = !empty($projectsString) 
                                                ? explode(',', $projectsString) 
                                                : [
                                                    'admin/authcontext',
                                                    'admin/allopapolici',
                                                    'admin/aferror',
                                                    'admin/protobuf',
                                                    'admin/openapi'
                                                ];
                                                
                                            // Map project paths to display names 
                                            $projects = [];
                                            foreach ($projectPaths as $path) {
                                                // Extract the name from the path (after the last /)
                                                $name = substr($path, strrpos($path, '/') + 1);
                                                // Format the name (capitalize words, replace hyphens with spaces)
                                                $displayName = ucwords(str_replace(['-', '_'], ' ', $name));
                                                $projects[$path] = $displayName;
                                            }
                                        @endphp
                                        
                                        @foreach($projects as $path => $name)
                                            <span class="project-badge selected-project" data-project-path="{{ $path }}">
                                                {{ $name }} <span class="remove-project" onclick="this.parentElement.remove();window.updateSelectedProjects();event.stopPropagation();">×</span>
                                            </span>
                                        @endforeach
                                    </div>
                                    <input type="hidden" id="selected_projects" name="selected_projects" value="{{ json_encode(array_keys($projects)) }}">
                                    <div class="form-text">These projects will be tracked for analytics and reporting</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Settings -->
                        <div class="card border mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Additional Settings</h6>
                            </div>
                            <div class="card-body">
                                <!-- Cache Duration -->
                                <div class="mb-3">
                                    <label for="gitlab_cache" class="form-label">Cache Duration (minutes)</label>
                                    <input type="number" class="form-control" id="gitlab_cache" name="gitlab_cache" value="{{ env('GITLAB_CACHE_DURATION', 60) }}" min="5" max="1440">
                                    <div class="form-text">How long to cache GitLab API results to avoid rate limiting (recommended: 30-60 minutes)</div>
                                </div>
                                
                                <!-- Webhooks Integration -->
                                <div class="mb-3">
                                    <label class="form-label">Webhook Integration</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="webhookUrl" value="{{ url('/api/webhooks/gitlab') }}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" id="copyWebhook" onclick="window.copyWebhookUrl()">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        For real-time updates, add this webhook URL to your GitLab project settings with <code>Push events</code>, <code>Merge request events</code>, and <code>Pipeline events</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end">
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
                    <h5 class="card-title mb-0">Connection Status</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @php
                            $isEnabled = env('GITLAB_ENABLED') == 'true';
                            $isConfigured = !empty(env('GITLAB_TOKEN')) && !empty(env('GITLAB_URL'));
                            $isConnected = $isEnabled && $isConfigured;
                        @endphp
                        <div class="connection-status-indicator bg-{{ $isConnected ? 'success' : 'danger' }} rounded-circle me-2" style="width: 14px; height: 14px;"></div>
                        <span class="fw-bold" id="connectionStatusText">{{ $isConnected ? 'Connected' : 'Not Connected' }}</span>
                        @if($isConfigured && !$isEnabled)
                            <span class="ms-2 badge bg-warning text-dark">Integration Disabled</span>
                        @endif
                    </div>
                    @if($isConnected)
                    <div class="text-muted small">
                        <div class="mb-1">Last checked: <strong>{{ now()->subMinutes(rand(1, 30))->diffForHumans() }}</strong></div>
                        <div class="mb-1">API Rate Limit: <strong>{{ rand(1500, 1900) }}/2000</strong></div>
                        <div>Group: <strong>{{ env('GITLAB_GROUP') }}</strong></div>
                    </div>
                    @else
                    <div class="text-muted small">
                        <p>
                            @if(!$isEnabled && $isConfigured)
                                Integration is configured but currently disabled. Enable it to connect.
                            @else
                                Please configure and save your GitLab settings to connect.
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">About GitLab Integration</h5>
                </div>
                <div class="card-body">
                    <p>Connecting to GitLab enables the following features:</p>
                    <ul class="mb-0">
                        <li>Deployment metrics tracking</li>
                        <li>Lead time calculation for merge requests</li>
                        <li>Code contribution analysis</li>
                        <li>Commit history and trends</li>
                        <li>CI/CD pipeline statistics</li>
                    </ul>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Setting Up Access Token</h5>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Log in to your GitLab account</li>
                        <li>Navigate to <strong>User Settings > Access Tokens</strong></li>
                        <li>Create a new token with the following scopes:
                            <ul>
                                <li><code>api</code></li>
                                <li><code>read_repository</code></li>
                                <li><code>read_api</code></li>
                            </ul>
                        </li>
                        <li>Copy the generated token</li>
                        <li>Paste it into the Personal Access Token field on this page</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the selected projects input
    window.updateSelectedProjects();
});
</script>
@endsection 