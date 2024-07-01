<div class="col-auto sidebar shadow-sm">
    <div style="margin-top: 20px;">
        @if(isset($project))
            <a href="{{route("project_show_page", $project->id)}}" class="nav-link text-white sidebar_project_title">
                <i class="bi bi-kanban-fill"></i>&nbsp;{{$project->title}}
            </a>
            <hr>
            <a href="{{route("repository_list_page", $project->id)}}" class="nav-link text-white menu_link">
                <i class="bi bi-server"></i>&nbsp;{{__('Repositories')}}
            </a>
            <a href="{{route("test_plan_list_page", $project->id)}}" class="nav-link text-white menu_link">
                <i class="bi bi-journals"></i>&nbsp;{{__('Test Plans')}}
            </a>
            <a href="{{route("test_run_list_page", $project->id)}}" class="nav-link text-white menu_link">
                <i class="bi bi-play-circle"></i>&nbsp;{{__('Test Runs')}}
            </a>
            <a href="{{route("project_documents_list_page", $project->id)}}" class="nav-link text-white">
                <i class="bi bi-file-text-fill"></i>&nbsp;{{__('Documents')}}
            </a>
            <hr>
        @endif
        <a href="{{route("project_list_page")}}" class="nav-link text-white menu_link">
            <i class="bi bi-diagram-3-fill"></i>&nbsp;{{__('All projects')}}
        </a>
        <a href="{{route('users_list_page')}}" class="nav-link text-white">
            <i class="bi bi-people-fill"></i>&nbsp;{{__('Users')}}
        </a>
        <hr>
        <a href="{{route('logout')}}" class="nav-link text-white">
            <i class="bi bi-box-arrow-in-left"></i>&nbsp;<b>{{__('Logout')}}</b>
        </a>
    </div>
</div>
