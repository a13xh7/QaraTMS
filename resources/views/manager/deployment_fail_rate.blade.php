@extends('layout.base_layout')

@section('title', 'Deployment Fail Rate')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Deployment Fail Rate</h1>
                <p class="text-muted">Track and analyze deployment failures across projects</p>
            </div>
            <a href="{{ route('manager.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <!-- Filter Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form class="row g-3 align-items-center">
                        <div class="col-md-3">
                            <label for="date-range" class="form-label">Time Period</label>
                            <select id="date-range" class="form-select">
                                <option value="last-30-days" selected>Last 30 Days</option>
                                <option value="last-90-days">Last 90 Days</option>
                                <option value="last-6-months">Last 6 Months</option>
                                <option value="last-year">Last Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="project" class="form-label">Project</label>
                            <select id="project" class="form-select">
                                <option value="all" selected>All Projects</option>
                                <option value="backend">Backend API</option>
                                <option value="frontend">Frontend Web</option>
                                <option value="mobile">Mobile App</option>
                                <option value="infrastructure">Infrastructure</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="environment" class="form-label">Environment</label>
                            <select id="environment" class="form-select">
                                <option value="all" selected>All Environments</option>
                                <option value="production">Production</option>
                                <option value="staging">Staging</option>
                                <option value="uat">UAT</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100">
                                <i class="bi bi-filter me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">Overall Fail Rate</h6>
                            <h3 class="fw-bold mb-0">8.4%</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-x-circle text-danger fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success"><i class="bi bi-arrow-down me-1"></i>2.1%</span>
                        <span class="text-muted ms-2">from last period</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">Total Deployments</h6>
                            <h3 class="fw-bold mb-0">342</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-rocket-takeoff text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success"><i class="bi bi-arrow-up me-1"></i>12.4%</span>
                        <span class="text-muted ms-2">from last period</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">Avg Recovery Time</h6>
                            <h3 class="fw-bold mb-0">42 min</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-alarm text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success"><i class="bi bi-arrow-down me-1"></i>18.5%</span>
                        <span class="text-muted ms-2">from last period</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-normal text-muted mb-0">Success Streak</h6>
                            <h3 class="fw-bold mb-0">16</h3>
                        </div>
                        <div class="bg-light p-3 rounded">
                            <i class="bi bi-trophy text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success"><i class="bi bi-arrow-up me-1"></i>4 deployments</span>
                        <span class="text-muted ms-2">from last streak</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Fail Rate Trend Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Deployment Fail Rate Trend</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="failRateTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Project Comparison & Failure By Type -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Fail Rate by Project</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="projectFailRateChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Failures by Type</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:300px;">
                        <canvas id="failureTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Deployment Failures Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Deployment Failures</h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">View All Failures</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Project</th>
                                    <th>Environment</th>
                                    <th>Failure Type</th>
                                    <th>Recovery Time</th>
                                    <th>Responsible Team</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2025-03-28 14:35</td>
                                    <td>Backend API</td>
                                    <td>Production</td>
                                    <td><span class="badge bg-danger">Database Migration</span></td>
                                    <td>28 min</td>
                                    <td>Database Team</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-03-25 09:12</td>
                                    <td>Frontend Web</td>
                                    <td>Staging</td>
                                    <td><span class="badge bg-warning">Build Error</span></td>
                                    <td>15 min</td>
                                    <td>Frontend Team</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-03-21 16:45</td>
                                    <td>Mobile App</td>
                                    <td>Production</td>
                                    <td><span class="badge bg-info">Config Issue</span></td>
                                    <td>52 min</td>
                                    <td>Mobile Team</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-03-18 11:20</td>
                                    <td>Infrastructure</td>
                                    <td>Production</td>
                                    <td><span class="badge bg-danger">Service Unavailable</span></td>
                                    <td>68 min</td>
                                    <td>DevOps Team</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-03-15 08:40</td>
                                    <td>Backend API</td>
                                    <td>QA</td>
                                    <td><span class="badge bg-warning">Test Failure</span></td>
                                    <td>12 min</td>
                                    <td>QA Team</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View Details</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <nav aria-label="Deployment failures pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('head')
<style>
    .avatar {
        font-weight: 500;
        text-transform: uppercase;
    }
    .chart-container {
        width: 100%;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sample data for charts
        const months = ['Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];
        
        // Fail Rate Trend Chart
        const trendCtx = document.getElementById('failRateTrendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Fail Rate (%)',
                    data: [12.5, 11.2, 9.8, 10.3, 9.1, 8.4],
                    backgroundColor: 'rgba(220, 53, 69, 0.2)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 20,
                        title: {
                            display: true,
                            text: 'Fail Rate (%)'
                        }
                    }
                }
            }
        });
        
        // Project Fail Rate Chart
        const projectCtx = document.getElementById('projectFailRateChart').getContext('2d');
        new Chart(projectCtx, {
            type: 'bar',
            data: {
                labels: ['Backend API', 'Frontend Web', 'Mobile App', 'Infrastructure'],
                datasets: [{
                    label: 'Fail Rate (%)',
                    data: [7.2, 6.8, 12.3, 9.1],
                    backgroundColor: [
                        'rgba(0, 123, 255, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(108, 117, 125, 0.7)'
                    ],
                    borderColor: [
                        'rgba(0, 123, 255, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Fail Rate (%)'
                        }
                    }
                }
            }
        });
        
        // Failure Type Chart
        const failureTypeCtx = document.getElementById('failureTypeChart').getContext('2d');
        new Chart(failureTypeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Database Issues', 'Build Errors', 'Config Problems', 'Service Unavailable', 'Test Failures', 'Other'],
                datasets: [{
                    data: [28, 22, 18, 15, 12, 5],
                    backgroundColor: [
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)',
                        'rgba(23, 162, 184, 0.7)',
                        'rgba(0, 123, 255, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(108, 117, 125, 0.7)'
                    ],
                    borderColor: [
                        'rgba(220, 53, 69, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(0, 123, 255, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(108, 117, 125, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    });
</script>
@endsection 