<div class="col-auto sidebar shadow-sm">
    <div class="sidebar-content py-4">
        @if(isset($project))
            <a href="{{route("project_show_page", $project->id)}}" class="nav-link text-white sidebar_project_title">
                <i class="bi bi-kanban-fill"></i>&nbsp;{{$project->title}}
            </a>
            <hr class="sidebar-divider">
            <div class="sidebar-section">
                <a href="{{route("repository_list_page", $project->id)}}" class="nav-link text-white menu_link">
                    <i class="bi bi-server"></i>&nbsp;{{__('Repositories')}}
                </a>
                <a href="{{route("test_plan_list_page", $project->id)}}" class="nav-link text-white menu_link">
                    <i class="bi bi-journals"></i>&nbsp;{{__('Test Plans')}}
                </a>
                <a href="{{route("test_run_list_page", $project->id)}}" class="nav-link text-white menu_link">
                    <i class="bi bi-play-circle"></i>&nbsp;{{__('Test Runs')}}
                </a>
                <a href="{{route("project_documents_list_page", $project->id)}}" class="nav-link text-white menu_link">
                    <i class="bi bi-file-text-fill"></i>&nbsp;{{__('Documents')}}
                </a>
            </div>
            <hr class="sidebar-divider">
        @endif
        <div class="sidebar-section">
            <a href="{{route("project_list_page")}}" class="nav-link text-white menu_link">
                <i class="bi bi-diagram-3-fill"></i>&nbsp;{{__('All projects')}}
            </a>
            <a href="{{route('users_list_page')}}" class="nav-link text-white menu_link">
                <i class="bi bi-people-fill"></i>&nbsp;{{__('Users')}}
            </a>
            
            <!-- Document Dropdown -->
            <div class="sidebar-dropdown">
                <div class="sidebar-dropdown-toggle gap-side-menu" data-dropdown-id="document">
                    <div>
                        <i class="bi bi-journal-text"></i>&nbsp;{{ __('Document') }}
                    </div>
                    <i class="bi bi-chevron-down sidebar-dropdown-icon"></i>
                </div>
                <div class="sidebar-dropdown-menu">
                    <a href="{{ route('documents.compliance') }}" class="nav-link text-white menu_link">
                        <i class="bi bi-shield-check"></i>&nbsp;{{ __('Compliance') }}
                    </a>
                    <a href="{{ route('documents.sop_qa') }}" class="nav-link text-white menu_link">
                        <i class="bi bi-file-earmark-text"></i>&nbsp;{{ __('SOP & QA Docs') }}
                    </a>
                    <a href="{{ route('documents.decision_logs') }}" class="nav-link text-white menu_link">
                        <i class="bi bi-journal-check"></i>&nbsp;{{ __('Decision Logs') }}
                    </a>
                    <a href="{{ route('documents.test_exceptions') }}" class="nav-link text-white menu_link">
                        <i class="bi bi-exclamation-diamond"></i>&nbsp;{{ __('Test Exceptions') }}
                    </a>
                    <a href="{{ route('documents.audit_readiness') }}" class="nav-link text-white menu_link">
                        <i class="bi bi-bar-chart"></i>&nbsp;{{ __('Audit Readiness') }}
                    </a>
                    <a href="{{ route('documents.knowledge_transfers') }}" class="nav-link text-white menu_link">
                        <i class="bi bi-arrow-repeat"></i>&nbsp;{{ __('Knowledge Transfers') }}
                    </a>
                </div>
            </div>

            <!-- Analytics Dashboard Dropdown -->
            @if(\App\Models\MenuVisibility::isVisible('analytics_dashboard'))
            <div class="sidebar-dropdown">
                <div class="sidebar-dropdown-toggle gap-side-menu" data-dropdown-id="analytics">
                    <div>
                        <i class="bi bi-graph-up"></i>&nbsp;{{__('Analytics Dashboard')}}
                    </div>
                    <i class="bi bi-chevron-down sidebar-dropdown-icon"></i>
                </div>
                
                <div class="sidebar-dropdown-menu">
                    <!-- Grafana Automation Report Dropdown -->
                    @if(\App\Models\MenuVisibility::isVisible('grafana_automation_report'))
                    <div class="sidebar-dropdown">
                        <div class="sidebar-dropdown-toggle gap-side-menu" data-dropdown-id="grafana">
                            <div>
                                <i class="bi bi-graph-up-arrow"></i>&nbsp;{{__('Grafana Automation Report')}}
                            </div>
                            <i class="bi bi-chevron-down sidebar-dropdown-icon"></i>
                        </div>
                        <div class="sidebar-dropdown-menu">
                            @if(\App\Models\MenuVisibility::isVisible('api_dashboard'))
                            <a href="{{route('api_dashboard')}}" class="nav-link text-white menu_link">
                                <i class="bi bi-code-slash"></i>&nbsp;{{__('API Automation Dashboard')}}
                            </a>
                            @endif
                            
                            @if(\App\Models\MenuVisibility::isVisible('apps_dashboard'))
                            <a href="{{route('apps_dashboard')}}" class="nav-link text-white menu_link">
                                <i class="bi bi-phone"></i>&nbsp;{{__('Apps Automation Dashboard')}}
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Defect Analytics Dropdown -->
                    @if(\App\Models\MenuVisibility::isVisible('defect_analytics_dashboard'))
                    <div class="sidebar-dropdown">
                        <div class="sidebar-dropdown-toggle gap-side-menu" data-dropdown-id="defects">
                            <div>
                                <i class="bi bi-bug"></i>&nbsp;{{__('Defect Analytics Dashboard')}}
                            </div>
                            <i class="bi bi-chevron-down sidebar-dropdown-icon"></i>
                        </div>
                        <div class="sidebar-dropdown-menu">
                            @if(\App\Models\MenuVisibility::isVisible('defect_analytics'))
                            <a href="{{route('defect_analytics')}}" class="nav-link text-white menu_link">
                                <i class="bi bi-graph-up"></i>&nbsp;{{__('Defect Analytics')}}
                            </a>
                            @endif
                            
                            @if(\App\Models\MenuVisibility::isVisible('bug_budget'))
                            <a href="{{route('bug_budget')}}" class="nav-link text-white menu_link">
                                <i class="bi bi-bug"></i>&nbsp;{{__('Bug Budget')}}
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if(\App\Models\MenuVisibility::isVisible('testing_progress'))
                    <a href="{{route('testing_progress')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-speedometer2"></i>&nbsp;{{__('Testing Progress')}}
                    </a>
                    @endif
                </div>
            </div>
            @endif
            
            <!-- Manager Dashboard Dropdown (only shown for authorized users) -->
            @can('access_manager_dashboard')
            <div class="sidebar-dropdown">
                <div class="sidebar-dropdown-toggle gap-side-menu" data-dropdown-id="manager-dashboard">
                    <div>
                        <i class="bi bi-bar-chart-fill"></i>&nbsp;{{__('Manager Dashboard')}}
                    </div>
                    <i class="bi bi-chevron-down sidebar-dropdown-icon"></i>
                </div>
                
                <div class="sidebar-dropdown-menu">
                    @if(\App\Models\MenuVisibility::isVisible('smoke_detector'))
                    <a href="{{route('manager.smoke_detector')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-fire"></i>&nbsp;{{__('Smoke Detector')}}
                    </a>
                    @endif
                    
                    @if(\App\Models\MenuVisibility::isVisible('post_mortems'))
                    <a href="{{route('manager.post_mortems')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-clipboard-data"></i>&nbsp;{{__('Post Mortems')}}
                    </a>
                    @endif
                    
                    @if(\App\Models\MenuVisibility::isVisible('monthly_contribution'))
                    <a href="{{route('manager.monthly_contribution')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-calendar-check"></i>&nbsp;{{__('Monthly Contribution MR')}}
                    </a>
                    @endif
                    
                    @if(\App\Models\MenuVisibility::isVisible('deployment_fail_rate'))
                    <a href="{{route('manager.deployment_fail_rate')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-exclamation-triangle"></i>&nbsp;{{__('Deployment Fail Rate')}}
                    </a>
                    @endif
                    
                    @if(\App\Models\MenuVisibility::isVisible('lead_time_mrs'))
                    <a href="{{route('manager.lead_time_mrs')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-clock-history"></i>&nbsp;{{__('Lead Time MRs')}}
                    </a>
                    @endif
                    
                    @if(\App\Models\MenuVisibility::isVisible('jira_lead_time'))
                    <a href="{{route('manager.jira_lead_time')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-kanban"></i>&nbsp;{{__('JIRA Lead Time')}}
                    </a>
                    @endif
                </div>
            </div>
            @endcan
            
            <!-- Settings Menu (only shown for authorized users) -->
            @can('access_settings')
            <div class="sidebar-dropdown">
                <div class="sidebar-dropdown-toggle gap-side-menu" data-dropdown-id="settings">
                    <div>
                        <i class="bi bi-gear-fill"></i>&nbsp;{{__('Settings')}}
                    </div>
                    <i class="bi bi-chevron-down sidebar-dropdown-icon"></i>
                </div>
                
                <div class="sidebar-dropdown-menu">
                    <a href="{{route('settings.jira')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-kanban"></i>&nbsp;{{__('Jira Settings')}}
                    </a>
                    <a href="{{route('settings.gitlab')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-git"></i>&nbsp;{{__('GitLab Settings')}}
                    </a>
                    <a href="{{route('settings.confluence')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-book"></i>&nbsp;{{__('Confluence Settings')}}
                    </a>
                    <a href="{{route('settings.dashboard_access')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-person-check"></i>&nbsp;{{__('Dashboard Access')}}
                    </a>
                    <a href="{{route('settings.settings_access')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-shield-lock"></i>&nbsp;{{__('Settings Access')}}
                    </a>
                    <a href="{{route('settings.menu_visibility')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-menu-button-wide"></i>&nbsp;{{__('Menu Visibility')}}
                    </a>
                    <a href="{{route('settings.advanced')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-sliders"></i>&nbsp;{{__('Advanced Settings')}}
                    </a>
                    <a href="{{route('settings.squad')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-people-fill"></i>&nbsp;{{__('Squad Settings')}}
                    </a>
                    <a href="{{route('settings.scoring')}}" class="nav-link text-white menu_link">
                        <i class="bi bi-bar-chart-line"></i>&nbsp;{{__('Scoring Settings')}}
                    </a>
                </div>
            </div>
            @endcan

        </div>
        <hr class="sidebar-divider">
        <a href="{{route('logout')}}" class="nav-link text-white menu_link">
            <i class="bi bi-box-arrow-in-left"></i>&nbsp;<b>{{__('Logout')}}</b>
        </a>
    </div>
</div>
