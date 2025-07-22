<header class="navbar sticky-top navbar-expand-lg shadow-sm">
    <div class="container-fluid px-3">
        <a class="navbar-brand d-flex align-items-center ms-2 gap-icon" href="/">
            <img src="{{ asset_path('img/logo.png') }}" alt="AF-TMS Logo" width="35" class="me-2">
            <span class="link-light">AF-TCMS</span>
        </a>

        <!-- Mobile toggle button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <div class="navbar-nav nav-pills me-auto mb-2 mb-lg-0">
                @if(Route::currentRouteName() == 'repository_show_page')
                    <!-- Project navigation links -->
                    <div class="d-flex flex-column flex-lg-row">
                        <a href="{{route("project_show_page", $project->id)}}" class="nav-link text-white">
                            <i class="bi bi-kanban-fill"></i>&nbsp;{{__('Dashboard')}}
                        </a>
                        <a href="{{route("repository_list_page", $project->id)}}" class="nav-link text-white">
                            <i class="bi bi-server"></i>&nbsp;{{__('Repositories')}}
                        </a>
                        <a href="{{route("test_plan_list_page", $project->id)}}" class="nav-link text-white">
                            <i class="bi bi-journals"></i>&nbsp;{{__('Test Plans')}}
                        </a>
                        <a href="{{route("test_run_list_page", $project->id)}}" class="nav-link text-white">
                            <i class="bi bi-play-circle"></i>&nbsp;{{__('Test Runs')}}
                        </a>

                        <div class="d-none d-lg-block border-start mx-2 my-1"></div>
                        <div class="d-lg-none my-2 border-bottom"></div>

                        <a href="{{route("project_documents_list_page", $project->id)}}" class="nav-link text-white">
                            <i class="bi bi-file-text-fill"></i>&nbsp;{{__('Documents')}}
                        </a>
                        <a href="{{route("project_list_page")}}" class="nav-link text-white">
                            <i class="bi bi-diagram-3-fill"></i>&nbsp;{{__('Projects')}}
                        </a>
                        <a href="{{route('users_list_page')}}" class="nav-link text-white">
                            <i class="bi bi-people-fill"></i>&nbsp;{{__('Users')}}
                        </a>
                        
                        @can('access_manager_dashboard')
                        <!-- Manager Dashboard Dropdown -->
                        <div class="d-none d-lg-block border-start mx-2 my-1"></div>
                        <div class="d-lg-none my-2 border-bottom"></div>
                        <div class="nav-item dropdown">
                            <a class="nav-link text-white dropdown-toggle {{ request()->routeIs(['manager.*']) ? 'active' : '' }}" 
                               href="#" id="managerDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bar-chart-fill"></i>&nbsp;{{__('Manager Dashboard')}}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="managerDropdown">
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.smoke_detector') ? 'active' : '' }}" 
                                       href="{{ route('manager.smoke_detector') }}">
                                        <i class="bi bi-fire"></i>&nbsp;{{__('Smoke Detector')}}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.post_mortems') ? 'active' : '' }}" 
                                       href="{{ route('manager.post_mortems') }}">
                                        <i class="bi bi-clipboard-data"></i>&nbsp;{{__('Post Mortems')}}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.monthly_contribution') ? 'active' : '' }}" 
                                       href="{{ route('manager.monthly_contribution') }}">
                                        <i class="bi bi-calendar-check"></i>&nbsp;{{__('Monthly Contribution')}}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.deployment_fail_rate') ? 'active' : '' }}" 
                                       href="{{ route('manager.deployment_fail_rate') }}">
                                        <i class="bi bi-exclamation-triangle"></i>&nbsp;{{__('Deployment Fail Rate')}}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.lead_time_mrs') ? 'active' : '' }}" 
                                       href="{{ route('manager.lead_time_mrs') }}">
                                        <i class="bi bi-clock-history"></i>&nbsp;{{__('Lead Time MRs')}}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.jira_lead_time') ? 'active' : '' }}" 
                                       href="{{ route('manager.jira_lead_time') }}">
                                        <i class="bi bi-kanban"></i>&nbsp;{{__('JIRA Lead Time')}}
                                    </a>
                                </li>
                            </ul>
                        </div>
                        @endcan
                    </div>
                @elseif(in_array(Route::currentRouteName(), ['dashboard', 'bug_budget', 'defect_analytics', 'testing_progress', 'api_dashboard', 'apps_dashboard']) || str_starts_with(Route::currentRouteName(), 'manager.'))
                    <!-- Dashboard navigation links -->
                    <div class="d-flex flex-column flex-lg-row">
                        <div class="nav-item item-link">
                            <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <i class="bi bi-graph-up"></i>&nbsp;{{__('Analytics Dashboard')}}
                            </a>
                        </div>
                        
                        <!-- Grafana Automation Report Dropdown -->
                        @php
                            $hasVisibleGrafanaItems = \App\Models\MenuVisibility::isVisible('api_dashboard') || 
                                                     \App\Models\MenuVisibility::isVisible('apps_dashboard');
                        @endphp
                        
                        @if($hasVisibleGrafanaItems)
                        <div class="nav-item dropdown">
                            <a class="nav-link text-white dropdown-toggle {{ request()->routeIs(['api_dashboard', 'apps_dashboard']) ? 'active' : '' }}" 
                               href="#" id="grafanaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-graph-up-arrow"></i>&nbsp;{{__('Grafana Automation Report')}}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="grafanaDropdown">
                                @if(\App\Models\MenuVisibility::isVisible('api_dashboard'))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('api_dashboard') ? 'active' : '' }}" 
                                       href="{{ route('api_dashboard') }}">
                                        <i class="bi bi-code-slash"></i>&nbsp;{{__('API Automation')}}
                                    </a>
                                </li>
                                @endif
                            
                                @if(\App\Models\MenuVisibility::isVisible('apps_dashboard'))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('apps_dashboard') ? 'active' : '' }}" 
                                       href="{{ route('apps_dashboard') }}">
                                        <i class="bi bi-phone"></i>&nbsp;{{__('Apps Automation')}}
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @endif

                        <!-- Defect Analytics Dropdown -->
                        @php
                            $hasVisibleDefectItems = \App\Models\MenuVisibility::isVisible('defect_analytics') || 
                                                   \App\Models\MenuVisibility::isVisible('bug_budget');
                        @endphp
                        
                        @if($hasVisibleDefectItems)
                        <div class="nav-item dropdown">
                            <a class="nav-link text-white dropdown-toggle {{ request()->routeIs(['defect_analytics', 'bug_budget']) ? 'active' : '' }}" 
                               href="#" id="defectDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bug"></i>&nbsp;{{__('Defect Analytics')}}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="defectDropdown">
                                @if(\App\Models\MenuVisibility::isVisible('defect_analytics'))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('defect_analytics') ? 'active' : '' }}" 
                                       href="{{ route('defect_analytics') }}">
                                        <i class="bi bi-graph-up"></i>&nbsp;{{__('Defect Analytics Dashboard')}}
                                    </a>
                                </li>
                                @endif
                                
                                @if(\App\Models\MenuVisibility::isVisible('bug_budget'))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('bug_budget') ? 'active' : '' }}" 
                                       href="{{ route('bug_budget') }}">
                                        <i class="bi bi-bug"></i>&nbsp;{{__('Bug Budget')}}
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @endif

                        @if(\App\Models\MenuVisibility::isVisible('testing_progress'))
                        <div class="nav-item item-link">
                            <a href="{{ route('testing_progress') }}" class="nav-link text-white {{ request()->routeIs('testing_progress') ? 'active' : '' }}">
                                <i class="bi bi-speedometer2"></i>&nbsp;{{__('Testing Progress')}}
                            </a>
                        </div>
                        @endif
                        
                        @can('access_manager_dashboard')
                        <!-- Manager Dashboard Dropdown -->
                        
                        @php
                            $hasVisibleManagerItems = \App\Models\MenuVisibility::isVisible('smoke_detector') || 
                                                    \App\Models\MenuVisibility::isVisible('post_mortems') ||
                                                    \App\Models\MenuVisibility::isVisible('monthly_contribution') ||
                                                    \App\Models\MenuVisibility::isVisible('deployment_fail_rate') ||
                                                    \App\Models\MenuVisibility::isVisible('lead_time_mrs') ||
                                                    \App\Models\MenuVisibility::isVisible('jira_lead_time');
                        @endphp
                        
                        @if($hasVisibleManagerItems)
                        <div class="nav-item dropdown">
                            <a class="nav-link text-white dropdown-toggle {{ request()->routeIs(['manager.*']) ? 'active' : '' }}" 
                               href="#" id="managerDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-bar-chart-fill"></i>&nbsp;{{__('Manager Dashboard')}}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="managerDropdown">
                                @if(\App\Models\MenuVisibility::isVisible('smoke_detector'))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.smoke_detector') ? 'active' : '' }}" 
                                       href="{{ route('manager.smoke_detector') }}">
                                        <i class="bi bi-fire"></i>&nbsp;{{__('Smoke Detector')}}
                                    </a>
                                </li>
                                @endif
                                
                                @if(\App\Models\MenuVisibility::isVisible('post_mortems'))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.post_mortems') ? 'active' : '' }}" 
                                       href="{{ route('manager.post_mortems') }}">
                                        <i class="bi bi-clipboard-data"></i>&nbsp;{{__('Post Mortems')}}
                                    </a>
                                </li>
                                @endif
                                
                                @if(\App\Models\MenuVisibility::isVisible('monthly_contribution'))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.monthly_contribution') ? 'active' : '' }}" 
                                       href="{{ route('manager.monthly_contribution') }}">
                                        <i class="bi bi-calendar-check"></i>&nbsp;{{__('Monthly Contribution')}}
                                    </a>
                                </li>
                                @endif
                                
                                @if(\App\Models\MenuVisibility::isVisible('deployment_fail_rate'))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.deployment_fail_rate') ? 'active' : '' }}" 
                                       href="{{ route('manager.deployment_fail_rate') }}">
                                        <i class="bi bi-exclamation-triangle"></i>&nbsp;{{__('Deployment Fail Rate')}}
                                    </a>
                                </li>
                                @endif
                                
                                @if(\App\Models\MenuVisibility::isVisible('lead_time_mrs'))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.lead_time_mrs') ? 'active' : '' }}" 
                                       href="{{ route('manager.lead_time_mrs') }}">
                                        <i class="bi bi-clock-history"></i>&nbsp;{{__('Lead Time MRs')}}
                                    </a>
                                </li>
                                @endif
                                
                                @if(\App\Models\MenuVisibility::isVisible('jira_lead_time'))
                                <li>
                                    <a class="dropdown-item {{ request()->routeIs('manager.jira_lead_time') ? 'active' : '' }}" 
                                       href="{{ route('manager.jira_lead_time') }}">
                                        <i class="bi bi-kanban"></i>&nbsp;{{__('JIRA Lead Time')}}
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                        @endif
                        @endcan
                        
                    </div>
                @endif
            </div>

            <div class="d-flex align-items-center">
                <a href="https://github.com/a13xh7/QARATMS" target="_blank" class="me-3" title="GitHub Repository">
                    <img src="{{ asset_path('img/github.png') }}" alt="GitHub" width="30">
                </a>
                <div class="d-flex align-items-center me-3">
                    <i class="bi bi-person-circle text-white fs-4 me-2"></i>
                    <div class="d-flex flex-column">
                        <span class="text-white">{{ Auth::user()->name }}</span>
                        <small class="text-white-50" style="display: flex; align-items: center; white-space: nowrap;">
                            @php
                                $name = strtolower(Auth::user()->name);
                                $role = match (true) {
                                    str_contains($name, 'master') => 'Administrator',
                                    str_contains($name, 'Manager') => 'Test Engineer Manager',
                                    in_array($name, ['Senior Test Engineer']) => 'Moderator (Test Engineer)',
                                    in_array($name, ['Test Engineer']) => 'Test Engineer',
                                    default => 'Guest'
                                };
                            @endphp
                            {{ $role }}
                        </small>
                    </div>
                </div>
                @if(Route::currentRouteName() == 'repository_show_page')
                    <a href="{{route('logout')}}" class="nav-link text-white">
                        <i class="bi bi-box-arrow-in-left"></i>&nbsp;<b>{{__('Logout')}}</b>
                    </a>
                @endif
            </div>
        </div>
    </div>
</header>
