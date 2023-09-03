<div class="col-auto sidebar shadow-sm">
    <div style="margin-top: 20px;">

        @if(isset($project))

            <a href="{{route("project_show_page", $project->id)}}" class="nav-link text-white sidebar_project_title">
                <i class="bi bi-kanban-fill"></i>
                {{$project->title}}
            </a>

            <hr>

            <a href="{{route("repository_list_page", $project->id)}}" class="nav-link text-white">
                <i class="bi bi-server"></i>
                {{ __('Repositories') }}
            </a>

            <a href="{{route("test_plan_list_page", $project->id)}}" class="nav-link text-white">
                <i class="bi bi-journals"></i> {{ __('Test Plans') }}
            </a>


            <a href="{{route("test_run_list_page", $project->id)}}" class="nav-link text-white">
                <i class="bi bi-play-circle"></i> {{ __('Test Runs') }}
            </a>



            <a href="{{route("project_documents_list_page", $project->id)}}" class="nav-link text-white">
                <i class="bi bi-file-text-fill"></i> {{ __(' Documents') }}
            </a>


            <hr>
        @endif


        <a href="{{route("project_list_page")}}" class="nav-link text-white">
            <i class="bi bi-diagram-3-fill"></i>
            {{ __('All projects') }}
        </a>


        <a href="{{route('users_list_page')}}" class="nav-link text-white">
            <i class="bi bi-people-fill"></i>
            {{ __('Users') }}
       </a>


       <hr>

       <a href="{{route('logout')}}" class="nav-link text-white">
            <i class="bi bi-box-arrow-in-left"></i>
            <b>{{ __('Logout') }}</b>
        </a>

    </div>

</div>
