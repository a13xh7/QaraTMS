<div class="col-auto sidebar-modern">
    <div class="sidebar-container">
        @if(isset($project))
            <!-- Project Header -->
            <div class="sidebar-header">
                <a href="{{route("project_show_page", $project->id)}}" class="sidebar-project-link">
                    <div class="project-icon">
                        <i class="bi bi-kanban-fill"></i>
                    </div>
                    <div class="project-info">
                        <h6 class="project-title">{{$project->title}}</h6>
                        <small class="project-subtitle">{{ __('Project Dashboard') }}</small>
                    </div>
                </a>
            </div>
            
            <!-- Project Navigation -->
            <div class="sidebar-section">
                <div class="sidebar-section-title">
                    <i class="bi bi-folder2-open me-2"></i>{{ __('Project Tools') }}
                </div>
                <nav class="sidebar-nav">
                    <a href="{{route("repository_list_page", $project->id)}}" class="sidebar-link {{ request()->routeIs('repository*') ? 'active' : '' }}">
                        <div class="link-icon">
                            <i class="bi bi-server"></i>
                        </div>
                        <span class="link-text">{{__('Repositories')}}</span>
                        <div class="link-indicator"></div>
                    </a>
                    
                    <a href="{{route("test_plan_list_page", $project->id)}}" class="sidebar-link {{ request()->routeIs('test_plan*') ? 'active' : '' }}">
                        <div class="link-icon">
                            <i class="bi bi-journals"></i>
                        </div>
                        <span class="link-text">{{__('Test Plans')}}</span>
                        <div class="link-indicator"></div>
                    </a>
                    
                    <a href="{{route("test_run_list_page", $project->id)}}" class="sidebar-link {{ request()->routeIs('test_run*') ? 'active' : '' }}">
                        <div class="link-icon">
                            <i class="bi bi-play-circle"></i>
                        </div>
                        <span class="link-text">{{__('Test Runs')}}</span>
                        <div class="link-indicator"></div>
                    </a>
                    
                    <a href="{{route("project_documents_list_page", $project->id)}}" class="sidebar-link {{ request()->routeIs('project_documents*') ? 'active' : '' }}">
                        <div class="link-icon">
                            <i class="bi bi-file-text-fill"></i>
                        </div>
                        <span class="link-text">{{__('Documents')}}</span>
                        <div class="link-indicator"></div>
                    </a>
                </nav>
            </div>
        @endif
        
        <!-- General Navigation -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">
                <i class="bi bi-gear-wide-connected me-2"></i>{{ __('Management') }}
            </div>
            <nav class="sidebar-nav">
                <a href="{{route("project_list_page")}}" class="sidebar-link {{ request()->routeIs('project_list*') ? 'active' : '' }}">
                    <div class="link-icon">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <span class="link-text">{{__('All projects')}}</span>
                    <div class="link-indicator"></div>
                </a>
                
                <a href="{{route('users_list_page')}}" class="sidebar-link {{ request()->routeIs('users*') ? 'active' : '' }}">
                    <div class="link-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <span class="link-text">{{__('Users')}}</span>
                    <div class="link-indicator"></div>
                </a>
            </nav>
        </div>
    </div>
</div>
