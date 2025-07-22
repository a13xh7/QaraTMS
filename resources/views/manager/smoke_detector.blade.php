@extends('layout.base_layout')

@section('title', 'Smoke Detector Dashboard')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Smoke Detector</h1>
                <p class="text-muted">Early warning system for potential issues across environments</p>
            </div>
            <div>
                <button id="refreshData" class="btn btn-outline-primary me-2">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh Data
                </button>
                <a href="{{ route('manager.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <label for="environment" class="form-label">Environment</label>
                            <select id="environment" class="form-select">
                                <option value="production" selected>Production</option>
                                <option value="staging">Staging</option>
                                <option value="uat">UAT</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="start-date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start-date" value="{{ date('Y-m-d', strtotime('-7 days')) }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end-date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end-date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i> Apply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 mb-4">
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="card-subtitle text-muted">Health Score</h6>
                        <div class="badge bg-success">{{ ucfirst($healthScore['status']) }}</div>
                    </div>
                    <h3 class="card-title display-6 mb-0">{{ $healthScore['current'] }}%</h3>
                    <p class="text-muted small mt-2">
                        @if($healthScore['current'] >= $healthScore['previous'])
                            <i class="bi bi-arrow-up-right text-success"></i> 
                            <span class="text-success">+{{ $healthScore['current'] - $healthScore['previous'] }}%</span>
                        @else
                            <i class="bi bi-arrow-down-right text-danger"></i> 
                            <span class="text-danger">{{ $healthScore['current'] - $healthScore['previous'] }}%</span>
                        @endif
                        from previous week
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="card-subtitle text-muted">API Response Time</h6>
                        <div class="badge bg-success">{{ ucfirst($responseTime['status']) }}</div>
                    </div>
                    <h3 class="card-title display-6 mb-0">{{ $responseTime['current'] }}ms</h3>
                    <p class="text-muted small mt-2">
                        @if($responseTime['current'] <= $responseTime['previous'])
                            <i class="bi bi-arrow-down-right text-success"></i> 
                            <span class="text-success">-{{ $responseTime['previous'] - $responseTime['current'] }}ms</span>
                        @else
                            <i class="bi bi-arrow-up-right text-danger"></i> 
                            <span class="text-danger">+{{ $responseTime['current'] - $responseTime['previous'] }}ms</span>
                        @endif
                        from previous week
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="card-subtitle text-muted">Error Rate</h6>
                        <div class="badge {{ $errorRate['status'] == 'elevated' ? 'bg-warning text-dark' : 'bg-success' }}">
                            {{ ucfirst($errorRate['status']) }}
                        </div>
                    </div>
                    <h3 class="card-title display-6 mb-0">{{ $errorRate['current'] }}%</h3>
                    <p class="text-muted small mt-2">
                        @if($errorRate['current'] <= $errorRate['previous'])
                            <i class="bi bi-arrow-down-right text-success"></i> 
                            <span class="text-success">-{{ $errorRate['previous'] - $errorRate['current'] }}%</span>
                        @else
                            <i class="bi bi-arrow-up-right text-danger"></i> 
                            <span class="text-danger">+{{ $errorRate['current'] - $errorRate['previous'] }}%</span>
                        @endif
                        from previous week
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="card-subtitle text-muted">Uptime</h6>
                        <div class="badge bg-success">{{ ucfirst($uptime['status']) }}</div>
                    </div>
                    <h3 class="card-title display-6 mb-0">{{ $uptime['current'] }}%</h3>
                    <p class="text-muted small mt-2">
                        @if($uptime['current'] >= $uptime['previous'])
                            <i class="bi bi-arrow-up-right text-success"></i> 
                            <span class="text-success">+{{ $uptime['current'] - $uptime['previous'] }}%</span>
                        @else
                            <i class="bi bi-arrow-down-right text-danger"></i> 
                            <span class="text-danger">{{ $uptime['current'] - $uptime['previous'] }}%</span>
                        @endif
                        from previous week
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Content Row -->
    <div class="row g-4 mb-4">
        <!-- Response Time Chart -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">API Response Time</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="responseTimeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Last 7 Days
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="responseTimeDropdown">
                            <li><a class="dropdown-item" href="#">Last 24 Hours</a></li>
                            <li><a class="dropdown-item active" href="#">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="responseTimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Error Rate Chart -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Error Rate by Service</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="errorRateDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Last 7 Days
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="errorRateDropdown">
                            <li><a class="dropdown-item" href="#">Last 24 Hours</a></li>
                            <li><a class="dropdown-item active" href="#">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="errorRateChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alert & Issues Row -->
    <div class="row g-4">
        <!-- Recent Alerts -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Recent Alerts</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($alerts as $index => $alert)
                        <div class="list-group-item list-group-item-action alert-item" data-alert-id="{{ $index + 1 }}">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <h6 class="mb-1 text-{{ $alert['type'] }}">
                                    <i class="bi {{ $alert['icon'] }} me-2"></i>{{ $alert['title'] }}
                                </h6>
                                <small class="text-muted">{{ $alert['time'] }}</small>
                            </div>
                            <p class="mb-1">{{ $alert['description'] }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">{{ $alert['service'] }}</small>
                                <span class="badge {{ $alert['statusClass'] }}">{{ $alert['status'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer bg-light text-center">
                    <a href="#" class="text-decoration-none">View All Alerts</a>
                </div>
            </div>
        </div>
        
        <!-- Top Issues -->
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Top Issues by Impact</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Issue</th>
                                    <th>Impact</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topIssues as $issue)
                                <tr>
                                    <td>{{ $issue['service'] }}</td>
                                    <td>{{ $issue['issue'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar {{ $issue['impact'] > 70 ? 'bg-danger' : ($issue['impact'] > 40 ? 'bg-warning' : 'bg-success') }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $issue['impact'] }}%;" 
                                                 aria-valuenow="{{ $issue['impact'] }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                    <td><span class="badge {{ $issue['statusClass'] }}">{{ $issue['status'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light text-center">
                    <a href="#" class="text-decoration-none">View All Issues</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alert Details Modal -->
    <div class="modal fade" id="alertDetailsModal" tabindex="-1" aria-labelledby="alertDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertDetailsModalLabel">Alert Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Take Action</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .alert-item {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .alert-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('footer')
<script>
    // API Response Time Chart
    const responseTimeCtx = document.getElementById('responseTimeChart').getContext('2d');
    let responseTimeChart = new Chart(responseTimeCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['responseTime']['days']) !!},
            datasets: [
                {
                    label: 'Response Time (ms)',
                    data: {!! json_encode($chartData['responseTime']['values']) !!},
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: 'p95 Response Time (ms)',
                    data: {!! json_encode($chartData['responseTime']['p95Values']) !!},
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    fill: true,
                    borderDash: [5, 5],
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Response Time (ms)'
                    }
                }
            }
        }
    });
    
    // Error Rate Chart
    const errorRateCtx = document.getElementById('errorRateChart').getContext('2d');
    let errorRateChart = new Chart(errorRateCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['errorRates']['services']) !!},
            datasets: [{
                label: 'Error Rate (%)',
                data: {!! json_encode($chartData['errorRates']['values']) !!},
                backgroundColor: [
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(25, 135, 84, 0.7)',
                    'rgba(255, 193, 7, 0.7)',
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(25, 135, 84, 0.7)',
                    'rgba(255, 193, 7, 0.7)'
                ],
                borderColor: [
                    'rgba(220, 53, 69, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(25, 135, 84, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(25, 135, 84, 1)',
                    'rgba(255, 193, 7, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Error Rate: ${context.raw}%`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 6,
                    title: {
                        display: true,
                        text: 'Error Rate (%)'
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
    
    // Alert Details Modal
    document.querySelectorAll('.alert-item').forEach(alert => {
        alert.addEventListener('click', function() {
            const alertId = this.dataset.alertId;
            const modal = new bootstrap.Modal(document.getElementById('alertDetailsModal'));
            modal.show();
            
            // Load alert details
            fetch(`{{ route('manager.alert_details', ['id' => ':id']) }}`.replace(':id', alertId))
                .then(response => response.json())
                .then(data => {
                    const modalBody = document.querySelector('#alertDetailsModal .modal-body');
                    
                    let timelineHtml = '';
                    if (data.timeline && data.timeline.length > 0) {
                        timelineHtml = '<div class="alert-timeline mt-4"><h6>Timeline</h6><ul class="list-group">';
                        data.timeline.forEach(item => {
                            timelineHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>${item.event}</span>
                                <span class="badge bg-secondary">${item.time}</span>
                            </li>`;
                        });
                        timelineHtml += '</ul></div>';
                    }
                    
                    modalBody.innerHTML = `
                        <h5>${data.title}</h5>
                        <p>${data.description}</p>
                        ${timelineHtml}
                    `;
                    
                    // If metrics data is available, create a chart
                    if (data.metrics) {
                        modalBody.innerHTML += '<div class="mt-4"><h6>Impact Analysis</h6><canvas id="alertMetricsChart" style="height: 200px;"></canvas></div>';
                        
                        const ctx = document.getElementById('alertMetricsChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: ['T-6', 'T-5', 'T-4', 'T-3', 'T-2', 'T-1', 'T'],
                                datasets: [
                                    {
                                        label: 'Before Mitigation',
                                        data: data.metrics.before,
                                        borderColor: '#dc3545',
                                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                        fill: true
                                    },
                                    {
                                        label: 'After Mitigation',
                                        data: data.metrics.after,
                                        borderColor: '#198754',
                                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                        fill: true
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    }
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching alert details:', error);
                    document.querySelector('#alertDetailsModal .modal-body').innerHTML = 
                        '<div class="alert alert-danger">Failed to load alert details. Please try again.</div>';
                });
        });
    });
    
    // Real-time data refresh
    document.getElementById('refreshData').addEventListener('click', function() {
        const button = this;
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-arrow-clockwise me-1 spin"></i> Refreshing...';
        
        fetch('{{ route("manager.real_time_data") }}')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update metrics
                    updateMetrics(data.data);
                    
                    // Update charts
                    updateCharts(data.data);
                    
                    // Show success toast
                    showToast('Data refreshed successfully', 'success');
                } else {
                    showToast('Failed to refresh data', 'danger');
                }
            })
            .catch(error => {
                console.error('Error fetching real-time data:', error);
                showToast('An error occurred while refreshing data', 'danger');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Refresh Data';
            });
    });
    
    function updateMetrics(data) {
        // Update health score
        document.querySelector('.card:nth-child(1) .card-title').innerText = data.healthScore.current + '%';
        updateDifference(
            document.querySelector('.card:nth-child(1) .text-muted.small'),
            data.healthScore.current, 
            data.healthScore.previous,
            true
        );
        
        // Update response time
        document.querySelector('.card:nth-child(2) .card-title').innerText = data.responseTime.current + 'ms';
        updateDifference(
            document.querySelector('.card:nth-child(2) .text-muted.small'),
            data.responseTime.current, 
            data.responseTime.previous,
            false
        );
        
        // Update error rate
        document.querySelector('.card:nth-child(3) .card-title').innerText = data.errorRate.current + '%';
        updateDifference(
            document.querySelector('.card:nth-child(3) .text-muted.small'),
            data.errorRate.current, 
            data.errorRate.previous,
            false
        );
        
        // Update uptime
        document.querySelector('.card:nth-child(4) .card-title').innerText = data.uptime.current + '%';
        updateDifference(
            document.querySelector('.card:nth-child(4) .text-muted.small'),
            data.uptime.current, 
            data.uptime.previous,
            true
        );
    }
    
    function updateDifference(element, current, previous, higherIsBetter) {
        const diff = higherIsBetter 
            ? current - previous 
            : previous - current;
        
        const isPositive = diff >= 0;
        const icon = isPositive
            ? (higherIsBetter ? 'bi-arrow-up-right text-success' : 'bi-arrow-down-right text-success')
            : (higherIsBetter ? 'bi-arrow-down-right text-danger' : 'bi-arrow-up-right text-danger');
        
        const textClass = isPositive ? 'text-success' : 'text-danger';
        const sign = isPositive ? '+' : '';
        
        element.innerHTML = `
            <i class="bi ${icon}"></i> 
            <span class="${textClass}">${sign}${Math.abs(diff)}${element.innerText.includes('ms') ? 'ms' : '%'}</span>
            from previous week
        `;
    }
    
    function updateCharts(data) {
        // Update response time chart
        responseTimeChart.data.datasets[0].data = data.chartData.responseTime.values;
        responseTimeChart.data.datasets[1].data = data.chartData.responseTime.p95Values;
        responseTimeChart.update();
        
        // Update error rate chart
        errorRateChart.data.datasets[0].data = data.chartData.errorRates.values;
        errorRateChart.update();
    }
    
    function showToast(message, type) {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast
        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.id = toastId;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        // Show toast
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();
        
        // Remove from DOM after hiding
        toast.addEventListener('hidden.bs.toast', function () {
            toast.remove();
        });
    }
    
    // Add spin animation
    document.head.insertAdjacentHTML('beforeend', `
        <style>
            .spin {
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        </style>
    `);
</script>
@endsection 