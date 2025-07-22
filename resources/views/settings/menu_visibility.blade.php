@extends('layout.base_layout')

@section('title', 'Menu Visibility Settings')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Menu Visibility Settings</h1>
                <p class="text-muted">Configure which menu items are visible in the sidebar</p>
            </div>
            <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Settings
            </a>
        </div>
    </div>
    
    <!-- Menu Visibility Settings -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Manager Dashboard Items</h5>
                    <span class="badge bg-primary">Manager Dashboard</span>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    <form method="POST" action="{{ route('settings.update_menu_visibility') }}">
                        @csrf
                        <p class="mb-3">Select which menu items should be visible in the sidebar. Changes are applied when you save.</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="smoke_detector" name="menu_items[]" value="smoke_detector" 
                                        {{ $menuItems->where('menu_key', 'smoke_detector')->first() && $menuItems->where('menu_key', 'smoke_detector')->first()->is_visible ? 'checked' : '' }}>
                                    <label class="form-check-label" for="smoke_detector">Smoke Detector</label>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="post_mortems" name="menu_items[]" value="post_mortems" 
                                        {{ $menuItems->where('menu_key', 'post_mortems')->first() && $menuItems->where('menu_key', 'post_mortems')->first()->is_visible ? 'checked' : '' }}>
                                    <label class="form-check-label" for="post_mortems">Post Mortems</label>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="deployment_fail_rate" name="menu_items[]" value="deployment_fail_rate" 
                                        {{ $menuItems->where('menu_key', 'deployment_fail_rate')->first() && $menuItems->where('menu_key', 'deployment_fail_rate')->first()->is_visible ? 'checked' : '' }}>
                                    <label class="form-check-label" for="deployment_fail_rate">Deployment Fail Rate</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="lead_time_mrs" name="menu_items[]" value="lead_time_mrs" 
                                        {{ $menuItems->where('menu_key', 'lead_time_mrs')->first() && $menuItems->where('menu_key', 'lead_time_mrs')->first()->is_visible ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lead_time_mrs">Lead Time MRs</label>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="jira_lead_time" name="menu_items[]" value="jira_lead_time" 
                                        {{ $menuItems->where('menu_key', 'jira_lead_time')->first() && $menuItems->where('menu_key', 'jira_lead_time')->first()->is_visible ? 'checked' : '' }}>
                                    <label class="form-check-label" for="jira_lead_time">JIRA Lead Time</label>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="monthly_contribution" name="menu_items[]" value="monthly_contribution" 
                                        {{ $menuItems->where('menu_key', 'monthly_contribution')->first() && $menuItems->where('menu_key', 'monthly_contribution')->first()->is_visible ? 'checked' : '' }}>
                                    <label class="form-check-label" for="monthly_contribution">Monthly Contribution MR</label>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="card-title mb-3">Analytics Dashboard Items</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3 form-check parent-menu">
                                    <input type="checkbox" class="form-check-input" id="analytics_dashboard" name="menu_items[]" value="analytics_dashboard" 
                                        {{ $menuItems->where('menu_key', 'analytics_dashboard')->first() && $menuItems->where('menu_key', 'analytics_dashboard')->first()->is_visible ? 'checked' : '' }}
                                        data-parent="true" data-children="grafana_automation_report,defect_analytics_dashboard,testing_progress">
                                    <label class="form-check-label fw-bold" for="analytics_dashboard">Analytics Dashboard (Parent)</label>
                                </div>
                                
                                <!-- Grafana Report Group -->
                                <div class="ms-4 mb-3">
                                    <div class="form-check parent-menu">
                                        <input type="checkbox" class="form-check-input" id="grafana_automation_report" name="menu_items[]" value="grafana_automation_report" 
                                            {{ $menuItems->where('menu_key', 'grafana_automation_report')->first() && $menuItems->where('menu_key', 'grafana_automation_report')->first()->is_visible ? 'checked' : '' }}
                                            data-parent="true" data-children="api_dashboard,apps_dashboard" data-parent-of="analytics_dashboard">
                                        <label class="form-check-label fw-bold" for="grafana_automation_report">Grafana Automation Report (Parent)</label>
                                    </div>
                                    
                                    <div class="ms-4">
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input child-menu" id="api_dashboard" name="menu_items[]" value="api_dashboard" 
                                                {{ $menuItems->where('menu_key', 'api_dashboard')->first() && $menuItems->where('menu_key', 'api_dashboard')->first()->is_visible ? 'checked' : '' }}
                                                data-parent-of="grafana_automation_report">
                                            <label class="form-check-label" for="api_dashboard">API Automation Dashboard</label>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input child-menu" id="apps_dashboard" name="menu_items[]" value="apps_dashboard" 
                                                {{ $menuItems->where('menu_key', 'apps_dashboard')->first() && $menuItems->where('menu_key', 'apps_dashboard')->first()->is_visible ? 'checked' : '' }}
                                                data-parent-of="grafana_automation_report">
                                            <label class="form-check-label" for="apps_dashboard">Apps Automation Dashboard</label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="testing_progress" name="menu_items[]" value="testing_progress" 
                                        {{ $menuItems->where('menu_key', 'testing_progress')->first() && $menuItems->where('menu_key', 'testing_progress')->first()->is_visible ? 'checked' : '' }}
                                        data-parent-of="analytics_dashboard">
                                    <label class="form-check-label" for="testing_progress">Testing Progress Dashboard</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Defect Analytics Group -->
                                <div class="mb-3">
                                    <div class="form-check parent-menu">
                                        <input type="checkbox" class="form-check-input" id="defect_analytics_dashboard" name="menu_items[]" value="defect_analytics_dashboard" 
                                            {{ $menuItems->where('menu_key', 'defect_analytics_dashboard')->first() && $menuItems->where('menu_key', 'defect_analytics_dashboard')->first()->is_visible ? 'checked' : '' }}
                                            data-parent="true" data-children="defect_analytics,bug_budget" data-parent-of="analytics_dashboard">
                                        <label class="form-check-label fw-bold" for="defect_analytics_dashboard">Defect Analytics Dashboard (Parent)</label>
                                    </div>
                                    
                                    <div class="ms-4">
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input child-menu" id="defect_analytics" name="menu_items[]" value="defect_analytics" 
                                                {{ $menuItems->where('menu_key', 'defect_analytics')->first() && $menuItems->where('menu_key', 'defect_analytics')->first()->is_visible ? 'checked' : '' }}
                                                data-parent-of="defect_analytics_dashboard">
                                            <label class="form-check-label" for="defect_analytics">Defect Analytics</label>
                                        </div>
                                        
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input child-menu" id="bug_budget" name="menu_items[]" value="bug_budget" 
                                                {{ $menuItems->where('menu_key', 'bug_budget')->first() && $menuItems->where('menu_key', 'bug_budget')->first()->is_visible ? 'checked' : '' }}
                                                data-parent-of="defect_analytics_dashboard">
                                            <label class="form-check-label" for="bug_budget">Bug Budget Dashboard</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                    <h5 class="card-title mb-0">Menu Settings</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Configure which menu items are visible in your application. Select items that are relevant to your team's workflow and requirements.</p>
                    
                    <div class="alert alert-primary mt-3 mb-0">
                        <div class="d-flex">
                            <div class="me-2">
                                <i class="bi bi-info-circle-fill"></i>
                            </div>
                            <div>
                                <p class="mb-0">Changes apply to all users with access to the respective dashboards.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Help</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Use these settings to customize which menu items appear in the sidebar. Unchecked items will be hidden from all users.</p>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> Select only the dashboards that your team actively uses for a cleaner interface.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('head')
<style>
    .form-check {
        padding-left: 2rem;
    }
    .form-check-input {
        margin-left: -2rem;
        width: 1.25rem;
        height: 1.25rem;
    }
    .form-check-label {
        padding-top: 2px;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Parent menu click handlers
        const parentMenus = document.querySelectorAll('.form-check-input[data-parent="true"]');
        parentMenus.forEach(parent => {
            parent.addEventListener('change', function() {
                // When a parent is unchecked, uncheck all its children
                if (!this.checked) {
                    const childrenIds = this.getAttribute('data-children').split(',');
                    childrenIds.forEach(childId => {
                        const childCheckbox = document.getElementById(childId);
                        if (childCheckbox) {
                            childCheckbox.checked = false;
                            // Trigger change event for nested parents
                            if (childCheckbox.getAttribute('data-parent') === 'true') {
                                const event = new Event('change');
                                childCheckbox.dispatchEvent(event);
                            }
                        }
                    });
                }
            });
        });
        
        // Child menu click handlers
        const childMenus = document.querySelectorAll('.form-check-input[data-parent-of]');
        childMenus.forEach(child => {
            child.addEventListener('change', function() {
                // When a child is checked, ensure its parent is checked
                if (this.checked) {
                    const parentId = this.getAttribute('data-parent-of');
                    const parentCheckbox = document.getElementById(parentId);
                    if (parentCheckbox && !parentCheckbox.checked) {
                        parentCheckbox.checked = true;
                        
                        // If this parent has its own parent, check that too
                        if (parentCheckbox.hasAttribute('data-parent-of')) {
                            const grandparentId = parentCheckbox.getAttribute('data-parent-of');
                            const grandparentCheckbox = document.getElementById(grandparentId);
                            if (grandparentCheckbox) {
                                grandparentCheckbox.checked = true;
                            }
                        }
                    }
                }
                // When a child is unchecked, check if all siblings are unchecked
                // If so, consider unchecking the parent (optional)
                else {
                    const parentId = this.getAttribute('data-parent-of');
                    const parentCheckbox = document.getElementById(parentId);
                    if (parentCheckbox && parentCheckbox.hasAttribute('data-children')) {
                        const siblingIds = parentCheckbox.getAttribute('data-children').split(',');
                        let allSiblingsUnchecked = true;
                        
                        siblingIds.forEach(siblingId => {
                            const siblingCheckbox = document.getElementById(siblingId);
                            if (siblingCheckbox && siblingCheckbox.checked) {
                                allSiblingsUnchecked = false;
                            }
                        });
                        
                        // Optional: Uncomment to auto-uncheck parent when all children are unchecked
                        // if (allSiblingsUnchecked) {
                        //     parentCheckbox.checked = false;
                        //     
                        //     // Trigger change event to cascade to grandparent if needed
                        //     const event = new Event('change');
                        //     parentCheckbox.dispatchEvent(event);
                        // }
                    }
                }
            });
        });

        // Auto-save when checkboxes are changed
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Submit the form automatically
                // document.querySelector('form').submit();
            });
        });
    });
</script>
@endsection 