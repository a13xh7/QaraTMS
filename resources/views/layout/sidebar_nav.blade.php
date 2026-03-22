<div class="col-1 sidebar shadow-sm">

    <div style="margin-top: 20px;">

        @if(isset($project))
            <div class="d-flex align-items-center">
                <!-- Квадратный аватар -->
                <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded-2 me-2 text-uppercase"
                     style="width: 36px; height: 36px; min-width: 36px; font-size: 0.85rem; font-weight: 600;">
                    {{mb_substr($project->title, 0, 2)}}
                </div>

                <!-- Название проекта -->
                <a href="{{route("project_show_page", $project->id)}}" class="nav-link text-white sidebar_project_title text-capitalize">
                    {{$project->title}}
                </a>
            </div>

            <hr>

            <a href="{{route("project_show_page", $project->id)}}" class="nav-link text-white sidebar_project_title">
                <i class="bi bi-kanban-fill"></i>&nbsp;Dashboard
            </a>

            <hr>

            <a href="{{route("repository_list_page", $project->id)}}" class="nav-link text-white menu_link">
                <i class="bi bi-server"></i>&nbsp;{{__('Repositories')}}
            </a>

            <ul>
                @foreach($project->repositories as $repository)
                    <li>
                        <a class="link-light" href="{{ route('repository_show_page', [$project->id, $repository->id]) }}">{{$repository->title}}</a>
                    </li>
                @endforeach
            </ul>

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
            <i class="bi bi-diagram-3-fill"></i>&nbsp;{{__('Projects')}}
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
