@extends('layout.base_layout')

@section('title', 'Manager Dashboard')

@section('content')
<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="display-5 fw-bold mb-0">Manager Dashboard</h1>
            <p class="text-muted">Access key performance metrics and reports for development and quality assurance</p>
        </div>
    </div>
    
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <!-- Smoke Detector Card -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Smoke Detector</h5>
                        <div class="icon-container bg-danger bg-opacity-10 text-danger rounded p-2">
                            <i class="bi bi-fire fs-4"></i>
                        </div>
                    </div>
                    <p class="card-text">Monitor system health and detect potential issues early in the development process.</p>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('manager.smoke_detector') }}" class="btn btn-outline-primary w-100">View Report</a>
                </div>
            </div>
        </div>
        
        <!-- Post Mortems Card -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Post Mortems</h5>
                        <div class="icon-container bg-info bg-opacity-10 text-info rounded p-2">
                            <i class="bi bi-clipboard-data fs-4"></i>
                        </div>
                    </div>
                    <p class="card-text">Review incident analysis and lessons learned from past deployment issues.</p>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('manager.post_mortems') }}" class="btn btn-outline-primary w-100">View Report</a>
                </div>
            </div>
        </div>
        
        <!-- Monthly Contribution MR Card -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Monthly Contribution MR</h5>
                        <div class="icon-container bg-success bg-opacity-10 text-success rounded p-2">
                            <i class="bi bi-calendar-check fs-4"></i>
                        </div>
                    </div>
                    <p class="card-text">Track monthly merge request contributions by team members and departments.</p>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('manager.monthly_contribution') }}" class="btn btn-outline-primary w-100">View Report</a>
                </div>
            </div>
        </div>
        
        <!-- Deployment Fail Rate Card -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Deployment Fail Rate</h5>
                        <div class="icon-container bg-warning bg-opacity-10 text-warning rounded p-2">
                            <i class="bi bi-exclamation-triangle fs-4"></i>
                        </div>
                    </div>
                    <p class="card-text">Monitor deployment success and failure rates across different environments.</p>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('manager.deployment_fail_rate') }}" class="btn btn-outline-primary w-100">View Report</a>
                </div>
            </div>
        </div>
        
        <!-- Lead Time MRs Card -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Lead Time MRs (excl. weekends)</h5>
                        <div class="icon-container bg-purple bg-opacity-10 text-purple rounded p-2">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                    </div>
                    <p class="card-text">Track the lead time for merge requests, excluding weekends, to measure efficiency.</p>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('manager.lead_time_mrs') }}" class="btn btn-outline-primary w-100">View Report</a>
                </div>
            </div>
        </div>
        
        <!-- JIRA Lead Time Card -->
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">JIRA Lead Time</h5>
                        <div class="icon-container bg-indigo bg-opacity-10 text-indigo rounded p-2">
                            <i class="bi bi-kanban fs-4"></i>
                        </div>
                    </div>
                    <p class="card-text">Analyze time taken for JIRA issues from creation to completion across projects.</p>
                </div>
                <div class="card-footer bg-transparent border-top-0">
                    <a href="{{ route('manager.jira_lead_time') }}" class="btn btn-outline-primary w-100">View Report</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('head')
<style>
    .text-purple { color: #6f42c1; }
    .text-indigo { color: #6610f2; }
    
    .icon-container {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endsection 