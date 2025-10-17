<header class="navbar sticky-top navbar-expand-lg">
    <div class="container-fluid px-4">
        <!-- Brand Section -->
        <div class="d-flex align-items-center">
            <a class="navbar-brand logo me-3" href="/">
                <img src="{{asset('img/logo.png')}}" alt="QaraTMS" width="40" height="40" class="rounded-2">
            </a>
            <a class="navbar-brand fw-bold gradient-text" href="/">TestFlow</a>
        </div>
        
        <!-- Main Navigation -->
        <div class="collapse navbar-collapse">
            <div class="navbar-nav nav-pills me-auto mb-2 mb-lg-0">
                @if(Route::currentRouteName() == 'repository_show_page')
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{route("project_show_page", $project->id)}}" class="nav-link text-white btn btn-outline-light btn-sm">
                            <i class="bi bi-kanban-fill me-1"></i>{{__('Dashboard')}}
                        </a>
                        <a href="{{route("repository_list_page", $project->id)}}" class="nav-link text-white btn btn-outline-light btn-sm">
                            <i class="bi bi-server me-1"></i>{{__('Repositories')}}
                        </a>
                        <a href="{{route("test_plan_list_page", $project->id)}}" class="nav-link text-white btn btn-outline-light btn-sm">
                            <i class="bi bi-journals me-1"></i>{{__('Test Plans')}}
                        </a>
                        <a href="{{route("test_run_list_page", $project->id)}}" class="nav-link text-white btn btn-outline-light btn-sm">
                            <i class="bi bi-play-circle me-1"></i>{{__('Test Runs')}}
                        </a>
                        
                        <div class="vr mx-2 opacity-50"></div>
                        
                        <a href="{{route("project_documents_list_page", $project->id)}}" class="nav-link text-white btn btn-outline-light btn-sm">
                            <i class="bi bi-file-text-fill me-1"></i>{{__('Documents')}}
                        </a>
                        <a href="{{route("project_list_page")}}" class="nav-link text-white btn btn-outline-light btn-sm">
                            <i class="bi bi-diagram-3-fill me-1"></i>{{__('Projects')}}
                        </a>
                        <a href="{{route('users_list_page')}}" class="nav-link text-white btn btn-outline-light btn-sm">
                            <i class="bi bi-people-fill me-1"></i>{{__('Users')}}
                        </a>
                    </div>
                @endif
            </div>
            
            <!-- Right Side Controls -->
            <div class="d-flex align-items-center gap-3">
                <!-- Theme Toggle Link -->
                <a href="#" id="theme-toggle" class="header-theme-mode btn btn-outline-light btn-sm" 
                   title="{{ __('ui.toggle_theme') }}" aria-label="{{ __('ui.toggle_theme') }}">
                    <i class="theme-icon bi bi-gear-fill"></i>
                    <span class="theme-label d-none d-lg-inline ms-1">{{ __('ui.auto_theme') }}</span>
                </a>
                
                <!-- Language Selector -->
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" 
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-globe me-1"></i>
                        <span class="d-none d-md-inline">{{ app()->getLocale() == 'en' ? 'English' : 'Español' }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                        <li>
                            <a class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}" 
                               href="?lang=en">
                                <img src="https://flagicons.lipis.dev/flags/4x3/us.svg" width="20" class="me-2">
                                English
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ app()->getLocale() == 'es' ? 'active' : '' }}" 
                               href="?lang=es">
                                <img src="https://flagicons.lipis.dev/flags/4x3/es.svg" width="20" class="me-2">
                                Español
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- GitHub Link -->
                <a href="https://github.com/javiandgo/QaraTMS" target="_blank" 
                   class="btn btn-outline-light btn-sm" title="View on GitHub">
                    <i class="bi bi-github"></i>
                </a>
                
                <!-- User Menu -->
                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i>
                            <span class="d-none d-md-inline">{{ auth()->user()->name ?? 'User' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                            <li>
                                <a class="dropdown-item" href="{{route('users_list_page')}}">
                                    <i class="bi bi-person-gear me-2"></i>Profile Settings
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{route('logout')}}">
                                    <i class="bi bi-box-arrow-right me-2"></i>{{__('Logout')}}
                                </a>
                            </li>
                        </ul>
                    </div>
            </div>
        </div>
    </div>
</header>
