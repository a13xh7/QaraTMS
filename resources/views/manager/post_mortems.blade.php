@extends('layout.base_layout')

@section('title', 'Post Mortems')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display-6 fw-bold mb-0">Post Mortems</h1>
                <p class="text-muted">Review incident analysis and lessons learned from past deployment issues</p>
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
                            <label for="time-period" class="form-label">Time Period</label>
                            <select id="time-period" class="form-select">
                                <option value="30days" selected>Last 30 Days</option>
                                <option value="90days">Last 90 Days</option>
                                <option value="6months">Last 6 Months</option>
                                <option value="1year">Last Year</option>
                                <option value="all">All Time</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="severity" class="form-label">Severity</label>
                            <select id="severity" class="form-select">
                                <option value="all" selected>All Severities</option>
                                <option value="critical">Critical</option>
                                <option value="major">Major</option>
                                <option value="minor">Minor</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="team" class="form-label">Team</label>
                            <select id="team" class="form-select">
                                <option value="all" selected>All Teams</option>
                                <option value="backend">Backend</option>
                                <option value="frontend">Frontend</option>
                                <option value="mobile">Mobile</option>
                                <option value="devops">DevOps</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Post Mortems List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Post Mortem Reports</h5>
                    <button class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> New Post Mortem
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Incident Date</th>
                                    <th>Title</th>
                                    <th>Duration</th>
                                    <th>Severity</th>
                                    <th>Lead</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2025-03-28</td>
                                    <td>Payment Gateway Outage</td>
                                    <td>1h 45m</td>
                                    <td><span class="badge bg-danger">Critical</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">JD</span>
                                            <span>John Doe</span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-03-15</td>
                                    <td>Database Connection Pool Exhaustion</td>
                                    <td>45m</td>
                                    <td><span class="badge bg-warning text-dark">Major</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">AS</span>
                                            <span>Alice Smith</span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-03-05</td>
                                    <td>API Rate Limiting Misconfiguration</td>
                                    <td>32m</td>
                                    <td><span class="badge bg-info">Minor</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">RJ</span>
                                            <span>Robert Johnson</span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2025-02-20</td>
                                    <td>CDN Configuration Error</td>
                                    <td>1h 15m</td>
                                    <td><span class="badge bg-warning text-dark">Major</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="avatar bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2" style="width: 30px; height: 30px; font-size: 12px;">EW</span>
                                            <span>Emma White</span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-primary">Draft</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">View</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <nav aria-label="Post mortems navigation">
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
</style>
@endsection 