@extends('layout.base_layout')

@section('content')
<div class="d-flex">
    @include('layout.sidebar_nav')

    <div class="flex-grow-1 main-content">
        <div class="container-fluid px-4 py-4">
            <!-- Breadcrumb and Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="page-title mb-1">Create New Test Run</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 small">
                            <li class="breadcrumb-item"><a href="{{ route('project_list_page') }}">Projects</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('project_show_page', $project->id) }}">{{ $project->name }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('test_run_list_page', $project->id) }}">Test Runs</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create New</li>
                        </ol>
                    </nav>
                </div>
                
                <div>
                    <a href="{{ route('test_run_list_page', $project->id) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Test Runs
                    </a>
                </div>
            </div>

            <!-- Alert Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span>Form Validation Error</span>
                    </h5>
                    <hr>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-8">
                    <!-- Main Form Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-play-circle me-2 text-primary"></i>
                                Test Run Details
                            </h5>
                        </div>
                        
                        <div class="card-body p-4">
                            <form action="{{ route('test_run_create') }}" method="POST" id="createTestRunForm">
                                @csrf
                                <input type="hidden" name="project_id" value="{{ $project->id }}">

                                <div class="mb-4">
                                    <label for="title" class="form-label">Test Run Name <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-tag"></i>
                                        </span>
                                        <input name="title" type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                               id="title" value="{{ old('title', 'Test Run') }}" required maxlength="100"
                                               placeholder="Enter a descriptive name for this test run">
                                    </div>
                                    <div class="form-text">
                                        Give your test run a clear, descriptive name (max 100 characters)
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="test_plan_id" class="form-label">Test Plan <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-clipboard-check"></i>
                                        </span>
                                        <select name="test_plan_id" id="test_plan_id" class="form-select form-select-lg @error('test_plan_id') is-invalid @enderror" required>
                                            <option disabled selected value> -- Select a Test Plan -- </option>
                                            @foreach($testPlans as $testPlan)
                                                <option value="{{ $testPlan->id }}" {{ old('test_plan_id') == $testPlan->id ? 'selected' : '' }}>
                                                    {{ $testPlan->title }} 
                                                    <small class="text-muted">({{ $testPlan->test_cases_count ?? 'Unknown' }} test cases)</small>
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-text">
                                        Select the test plan that contains the test cases you want to run
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3"
                                              placeholder="Optional description of this test run">{{ old('description') }}</textarea>
                                    <div class="form-text">
                                        Provide additional context or goals for this test run (optional)
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label d-block">Options</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="assignToMe" name="assign_to_me" checked>
                                        <label class="form-check-label" for="assignToMe">Assign this test run to me</label>
                                    </div>
                                    
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="includeAutomated" name="include_automated" checked>
                                        <label class="form-check-label" for="includeAutomated">Include automated test cases</label>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between border-top pt-4">
                                    <a href="{{ route('test_run_list_page', $project->id) }}" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-lg me-1"></i> Cancel
                                    </a>
                                    
                                    <div>
                                        <button type="submit" name="save_and_run" value="1" class="btn btn-success px-4 me-2">
                                            <i class="bi bi-play-fill me-1"></i> Create & Start Testing
                                        </button>
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-save me-1"></i> Create Test Run
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Help Card -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle me-2 text-info"></i>
                                Quick Help
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3 text-muted">Creating a Test Run</h6>
                            <p class="card-text small">A test run is an execution of a test plan. It allows you to track the progress and results of your testing efforts.</p>
                            
                            <hr>
                            
                            <h6 class="mb-2">Tips:</h6>
                            <ul class="small mb-0">
                                <li class="mb-2">Choose a descriptive name that includes version or sprint information</li>
                                <li class="mb-2">Select a test plan that contains all the test cases you need to execute</li>
                                <li class="mb-2">Add a description to provide context for other team members</li>
                                <li>You can start testing immediately after creating the test run</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Recent Test Runs Card -->
                    @if(isset($recentTestRuns) && count($recentTestRuns) > 0)
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">
                                <i class="bi bi-clock-history me-2 text-secondary"></i>
                                Recent Test Runs
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach($recentTestRuns as $recentRun)
                                <li class="list-group-item">
                                    <a href="{{ route('test_run_show_page', [$project->id, $recentRun->id]) }}" class="text-decoration-none">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 text-truncate" style="max-width: 200px;">{{ $recentRun->title }}</h6>
                                                <small class="text-muted">{{ $recentRun->created_at->diffForHumans() }}</small>
                                            </div>
                                            <span class="badge bg-{{ $recentRun->status_color ?? 'secondary' }} rounded-pill">
                                                {{ $recentRun->status_text ?? 'In Progress' }}
                                            </span>
                                        </div>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <a href="{{ route('test_run_list_page', $project->id) }}" class="btn btn-sm btn-outline-primary">
                                View All Test Runs
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.main-content {
    min-height: 100vh;
    padding-top: 0;
    background-color: #f8f9fa;
}

.page-title {
    font-weight: 600;
    color: #333;
}

.card {
    border-radius: 6px;
    transition: box-shadow 0.2s ease;
}

.card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.card-header {
    border-top-left-radius: 6px !important;
    border-top-right-radius: 6px !important;
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-control-lg, .form-select-lg {
    font-size: 1rem;
}

.btn {
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.alert {
    border-radius: 6px;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "›";
}

.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.getElementById('createTestRunForm');
    
    form.addEventListener('submit', function(event) {
        let isValid = true;
        
        // Validate title
        const titleInput = document.getElementById('title');
        if (!titleInput.value.trim()) {
            titleInput.classList.add('is-invalid');
            isValid = false;
            
            // Create error message if it doesn't exist
            if (!document.getElementById('title-error')) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'title-error';
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Test run name is required';
                titleInput.parentNode.appendChild(errorDiv);
            }
        } else {
            titleInput.classList.remove('is-invalid');
        }
        
        // Validate test plan selection
        const testPlanSelect = document.getElementById('test_plan_id');
        if (!testPlanSelect.value) {
            testPlanSelect.classList.add('is-invalid');
            isValid = false;
            
            // Create error message if it doesn't exist
            if (!document.getElementById('test-plan-error')) {
                const errorDiv = document.createElement('div');
                errorDiv.id = 'test-plan-error';
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = 'Please select a test plan';
                testPlanSelect.parentNode.appendChild(errorDiv);
            }
        } else {
            testPlanSelect.classList.remove('is-invalid');
        }
        
        if (!isValid) {
            event.preventDefault();
        }
    });
    
    // Clear validation errors when input changes
    const inputs = document.querySelectorAll('.form-control, .form-select');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
        
        input.addEventListener('change', function() {
            this.classList.remove('is-invalid');
        });
    });
    
    // Generate a default name based on date if field is empty or default
    const titleInput = document.getElementById('title');
    if (titleInput.value === 'Test Run') {
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0]; // YYYY-MM-DD
        titleInput.value = `Test Run - ${formattedDate}`;
    }
});
</script>
@endsection


