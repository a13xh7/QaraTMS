<!-- Dashboard Navigation Bar -->
<div class="dashboard-nav-container">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid px-0">
            <div class="collapse navbar-collapse" id="dashboardNavbar">
                <ul class="navbar-nav me-auto">
                    <!-- <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-graph-up"></i> Analytics Dashboard
                        </a>
                    </li> -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('bug_budget') ? 'active' : '' }}" href="{{ route('bug_budget') }}">
                            <i class="bi bi-bug"></i> Bug Budget
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('defect_analytics') ? 'active' : '' }}" href="{{ route('defect_analytics') }}">
                            <i class="bi bi-graph-up"></i> Defect Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('testing_progress') ? 'active' : '' }}" href="{{ route('testing_progress') }}">
                            <i class="bi bi-speedometer2"></i> Testing Progress
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('apps_dashboard') ? 'active' : '' }}" href="{{ route('apps_dashboard') }}">
                            <i class="bi bi-phone"></i> Apps Automation
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('api_dashboard') ? 'active' : '' }}" href="{{ route('api_dashboard') }}">
                            <i class="bi bi-code-slash"></i> API Automation
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('project_list_page') }}">
                            <i class="bi bi-arrow-left"></i> Back to Projects
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</div> 