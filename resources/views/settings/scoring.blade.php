@extends('layout.base_layout')

@section('title', 'Scoring Settings')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Scoring Settings</h1>
                <p class="text-muted">Configure performance metric weights and scoring criteria</p>
            </div>
            <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Settings
            </a>
        </div>
    </div>
    
    <!-- Scoring Settings -->
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Performance Metrics</h5>
                    <span class="badge bg-success">Scoring</span>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    <form method="POST" action="{{ route('settings.update_scoring') }}">
                        @csrf
                        <div class="mb-4">
                            <h6 class="mb-3">Metric Weight Distribution</h6>
                            <p class="text-muted mb-4">Configure the weight of each metric in the overall performance score. Total should add up to 100%.</p>
                            
                            <div class="mb-4">
                                <label for="quality_weight" class="form-label">Quality Weight (%)</label>
                                <div class="d-flex align-items-center">
                                    <input type="range" class="form-range me-2" min="0" max="100" step="5" 
                                           id="quality_weight" name="quality_weight" value="{{ env('QUALITY_WEIGHT', 40) }}" 
                                           oninput="document.getElementById('quality_weight_value').innerText = this.value + '%'">
                                    <span class="badge bg-primary" id="quality_weight_value" style="width: 60px;">{{ env('QUALITY_WEIGHT', 40) }}%</span>
                                </div>
                                <div class="form-text">Weight for code quality metrics (test coverage, code smells, bugs)</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="speed_weight" class="form-label">Speed Weight (%)</label>
                                <div class="d-flex align-items-center">
                                    <input type="range" class="form-range me-2" min="0" max="100" step="5" 
                                           id="speed_weight" name="speed_weight" value="{{ env('SPEED_WEIGHT', 30) }}" 
                                           oninput="document.getElementById('speed_weight_value').innerText = this.value + '%'">
                                    <span class="badge bg-primary" id="speed_weight_value" style="width: 60px;">{{ env('SPEED_WEIGHT', 30) }}%</span>
                                </div>
                                <div class="form-text">Weight for development speed metrics (lead time, cycle time)</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="contribution_weight" class="form-label">Contribution Weight (%)</label>
                                <div class="d-flex align-items-center">
                                    <input type="range" class="form-range me-2" min="0" max="100" step="5" 
                                           id="contribution_weight" name="contribution_weight" value="{{ env('CONTRIBUTION_WEIGHT', 30) }}" 
                                           oninput="document.getElementById('contribution_weight_value').innerText = this.value + '%'">
                                    <span class="badge bg-primary" id="contribution_weight_value" style="width: 60px;">{{ env('CONTRIBUTION_WEIGHT', 30) }}%</span>
                                </div>
                                <div class="form-text">Weight for contribution metrics (commits, reviews, comments)</div>
                            </div>
                            
                            <div class="alert alert-primary d-flex align-items-center" role="alert">
                                <div>
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    <span>Total Weight: <span id="total_weight" class="fw-bold">100</span>%</span>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="mb-4">
                            <h6 class="mb-3">Grade Thresholds</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">A Grade Threshold (%)</label>
                                    <input type="number" class="form-control" name="grade_a" value="90" min="0" max="100">
                                    <div class="form-text">Minimum score for A grade</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">B Grade Threshold (%)</label>
                                    <input type="number" class="form-control" name="grade_b" value="80" min="0" max="100">
                                    <div class="form-text">Minimum score for B grade</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">C Grade Threshold (%)</label>
                                    <input type="number" class="form-control" name="grade_c" value="70" min="0" max="100">
                                    <div class="form-text">Minimum score for C grade</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">D Grade Threshold (%)</label>
                                    <input type="number" class="form-control" name="grade_d" value="60" min="0" max="100">
                                    <div class="form-text">Minimum score for D grade</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Scoring Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Scoring Preview</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="display-1 fw-bold">B+</div>
                        <div class="text-muted">Sample Team Grade</div>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Metric Breakdown</h6>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Quality Score</span>
                            <span>85%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Speed Score</span>
                            <span>75%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Contribution Score</span>
                            <span>90%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Overall Score</span>
                            <span>83%</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Help</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Use these settings to configure how team performance is scored. Adjust the weight of each metric to prioritize different aspects of development.</p>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle-fill me-2"></i> The total weight of all metrics should add up to 100%.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update total weight calculation
        const qualityInput = document.getElementById('quality_weight');
        const speedInput = document.getElementById('speed_weight');
        const contributionInput = document.getElementById('contribution_weight');
        const totalWeightElement = document.getElementById('total_weight');
        
        function updateTotalWeight() {
            const quality = parseInt(qualityInput.value) || 0;
            const speed = parseInt(speedInput.value) || 0;
            const contribution = parseInt(contributionInput.value) || 0;
            
            const total = quality + speed + contribution;
            totalWeightElement.innerText = total;
            
            if (total !== 100) {
                totalWeightElement.classList.add('text-danger');
            } else {
                totalWeightElement.classList.remove('text-danger');
            }
        }
        
        qualityInput.addEventListener('input', updateTotalWeight);
        speedInput.addEventListener('input', updateTotalWeight);
        contributionInput.addEventListener('input', updateTotalWeight);
        
        // Initialize total weight
        updateTotalWeight();
    });
</script>
@endsection 