<div class="col-auto sidebar shadow-sm">
    <div style="margin-top: 20px;">

        <a href="{{route("project_show_page", $project->id)}}" class="nav-link text-white sidebar_project_title">
            <i class="bi bi-arrow-right-square-fill"></i>
            {{$project->title}}
        </a>

        <hr>

        <a href="{{route("repository_list_page", $project->id)}}" class="nav-link text-white">
            <i class="bi bi-server"></i>
            Repositories
        </a>

        <a href="{{route("test_plan_list_page", $project->id)}}" class="nav-link text-white">
            <i class="bi bi-journals"></i> Test Plans
        </a>


        <a href="{{route("test_run_list_page", $project->id)}}" class="nav-link text-white">
            <i class="bi bi-play-circle"></i> Test Runs
        </a>

        <hr>

        <a href="{{route("project_documents_list_page", $project->id)}}" class="nav-link text-white">
            <i class="bi bi-file-text-fill"></i> Documents
        </a>
    </div>

</div>
