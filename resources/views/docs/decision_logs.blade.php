@extends('layout.base_layout')

@section('head')
    <link href="{{ asset_path('css/docs.css') }}" rel="stylesheet">
    <style>
        /* Modern Documentation Page Styles for Decision Logs */
        .modern-doc-container {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: calc(100vh - 60px);
            padding: 2rem 0;
        }
        .modern-doc-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 0 1rem;
        }
        .modern-doc-title {
            font-size: 3rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.5rem;
            letter-spacing: -0.025em;
        }
        .modern-doc-subtitle {
            font-size: 1.125rem;
            color: #64748b;
            font-weight: 400;
            margin-bottom: 2rem;
        }
        .modern-search-container {
            max-width: 700px;
            margin: 0 auto 3rem;
            position: relative;
        }
        .modern-search-bar {
            background: white;
            border-radius: 50px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07);
            padding: 1.5rem 2rem 1.5rem 2rem;
            margin-bottom: 0;
        }
        .modern-content-area {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 400px;
            padding: 0 1rem;
        }
        .modern-main-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.10), 0 10px 10px -5px rgba(0,0,0,0.04);
            width: 100%;
            max-width: 1200px;
            border: 1px solid #f1f5f9;
        }
        @media (max-width: 900px) {
            .modern-main-card { padding: 1.5rem 0.5rem; }
            .modern-doc-title { font-size: 2rem; }
        }
        @media (max-width: 600px) {
            .modern-main-card { padding: 1rem 0.2rem; }
            .modern-doc-header { margin-bottom: 2rem; }
        }
    </style>
@endsection

@section('content')
    @include('layout.sidebar_nav')
    <div class="col fh">
        <div class="modern-doc-container">
            <div class="modern-doc-header">
                <h1 class="modern-doc-title"><i class="bi bi-journal-check"></i> Decision Logs</h1>
                <p class="modern-doc-subtitle">Track, review, and manage all key QA and process decisions in one place.</p>
                <div class="modern-search-container">
                    <div class="modern-search-bar">
                        <!-- Place the filter toolbar here for a modern look -->
                        <div class="filter-toolbar mb-0">
                            <form method="GET" action="{{ route('documents.decision_logs') }}">
                                <div class="d-flex flex-wrap gap-3 align-items-end">
                                    <div class="filter-group">
                                        <label class="filter-label">Search</label>
                                        <input type="text" name="search" class="filter-input" placeholder="Search titles, owners, tags..." value="{{ request('search') }}">
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">Decision Type</label>
                                        <select name="decision_type" class="filter-input">
                                            <option value="">All Types</option>
                                            @foreach($allTypes as $type)
                                                <option value="{{ $type }}" {{ request('decision_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">Status</label>
                                        <select name="status" class="filter-input">
                                            <option value="">All Statuses</option>
                                            <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="Finalized" {{ request('status') == 'Finalized' ? 'selected' : '' }}>Finalized</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">Start Date</label>
                                        <input type="date" name="start_date" class="filter-input" value="{{ request('start_date') }}">
                                    </div>
                                    <div class="filter-group">
                                        <label class="filter-label">End Date</label>
                                        <input type="date" name="end_date" class="filter-input" value="{{ request('end_date') }}">
                                    </div>
                                    <div class="filter-group">
                                        <button type="submit" class="filter-button">
                                            <i class="fas fa-search"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modern-content-area">
                <div class="modern-main-card">
                    <!-- All original Decision Logs content below, except the filter toolbar which is now above -->
                    <div class="summary-header mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h4 mb-0">Decision Logs</h2>
                                <p class="text-muted mb-0">
                                    {{ $decisionLogs->total() }} Decisions 
                                    ({{ $statusCounts['Draft'] ?? 0 }} Drafts, 
                                     {{ $statusCounts['Finalized'] ?? 0 }} Finalized)
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-secondary" onclick="exportAllDecisions()">
                                    <i class="fas fa-download"></i> Export All
                                </button>
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDecisionLogModal">
                                    <i class="fas fa-plus"></i> Add Decision Log
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- View Mode Toggle (outside the form, as real buttons) -->
                    <div class="view-mode-toggle mb-3" style="justify-content: flex-end;">
                        <button type="button" class="view-mode-btn active" id="fullViewBtn">Full</button>
                        <button type="button" class="view-mode-btn" id="compactViewBtn">Compact</button>
                    </div>

                    <!-- Horizontal Table of Contents Summary -->
                    <div class="toc-horizontal-summary mb-4">
                        <div class="d-flex flex-wrap gap-4 align-items-center justify-content-start">
                            <!-- Status Badges -->
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold text-secondary me-2">Status:</span>
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis px-3 py-2">
                                    Draft <span class="fw-bold ms-1">{{ $statusCounts['Draft'] ?? 0 }}</span>
                                </span>
                                <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-3 py-2">
                                    Finalized <span class="fw-bold ms-1">{{ $statusCounts['Finalized'] ?? 0 }}</span>
                                </span>
                            </div>
                            <!-- Decision Types Badges -->
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-semibold text-secondary me-2">Decision Types:</span>
                                @foreach($allTypes as $type)
                                    <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-3 py-2">
                                        {{ $type }} <span class="fw-bold ms-1">{{ $typeCounts[$type] ?? 0 }}</span>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    <!-- Bulk Actions -->
                    <div class="bulk-actions" id="bulkActions">
                        <span class="selected-count">0 items selected</span>
                        <button class="btn btn-outline-secondary btn-sm" onclick="exportSelected()">
                            <i class="fas fa-download"></i> Export Selected
                        </button>
                        <button class="btn btn-outline-danger btn-sm" onclick="deleteSelected()">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                    @php
                    // Group logs by a hash of their main fields to collapse duplicates
                    $groupedLogs = collect($decisionLogs->items())->groupBy(function($log) {
                        return md5(
                            $log->title . '|' . $log->decision_type . '|' . $log->decision_owner . '|' . $log->decision_date . '|' . $log->context . '|' . $log->decision . '|' . $log->impact_risk . '|' . json_encode($log->related_artifacts)
                        );
                    });
                    @endphp
                    <!-- Decision Logs Cards -->
                    <div class="space-y-4">
                    @forelse($groupedLogs as $hash => $logs)
                        @php 
                            $log = $logs->first();
                            // Deduplicate artifacts by name+size
                            $allArtifacts = collect($logs)->flatMap(function($l) { return $l->related_artifacts ?? []; });
                            $uniqueArtifacts = $allArtifacts->unique(function($a) { return ($a['name'] ?? '') . '-' . ($a['size'] ?? 0); })->values();
                            $hasFinalized = $logs->contains(function($l) { return strtolower($l->status) === 'finalized'; });
                        @endphp
                        <div class="decision-log-card {{ strtolower($log->status) }} {{ $loop->even ? 'bg-white' : 'bg-[#fffdef]' }}" id="log-{{ $log->id }}"
                            @if(request('view') === 'compact') onclick="expandCard({{ $log->id }})" style="cursor:pointer;" @endif>
                            <div class="decision-log-header">
                                <div class="d-flex align-items-center gap-3">
                                    <input type="checkbox" class="form-check-input log-checkbox" value="{{ $log->id }}" onchange="updateBulkActions()">
                                    <div>
                                        <h2 class="decision-log-title">{{ $log->title }}</h2>
                                        <div class="decision-log-time">
                                            <i class="fas fa-clock"></i> {{ $log->decision_date ? $log->decision_date->format('d M Y') : '—' }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <span class="owner-avatar">{{ substr($log->decision_owner ?? 'Unknown', 0, 1) }}</span>
                                            {{ $log->decision_owner ?? 'Unknown' }}
                                            @if($logs->count() > 1)
                                                <span class="badge bg-blue-100 text-blue-700">{{ $logs->count() }} similar logs</span>
                                            @endif
                                            @if($hasFinalized)
                                                <span class="badge bg-green-100 text-green-700 ms-2"><i class="fas fa-check-circle"></i> Finalized</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="decision-log-actions">
                                    <span class="status-badge {{ strtolower($log->status) }}">
                                        {{ $log->status }}
                                    </span>
                                    <button class="compact-toggle" onclick="toggleCardDetails({{ $log->id }})" id="toggle-{{ $log->id }}" title="Show Details">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <div class="dropdown">
                                        <button class="action-button dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-toggle="tooltip" title="More options">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="editDecisionLog({{ $log->id }})">
                                                <i class="fas fa-edit"></i> Edit
                                            </a></li>
                                            <li><a class="dropdown-item" href="{{ route('documents.decision_logs.pdf', $log->id) }}">
                                                <i class="fas fa-download"></i> Export PDF
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteDecisionLog({{ $log->id }})">
                                                <i class="fas fa-trash"></i> Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-content" id="content-{{ $log->id }}">
                                <div class="decision-log-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">Decision Type:</span>
                                        <span class="meta-value">{{ $log->decision_type ?? '—' }}</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Sprint/Release:</span>
                                        <span class="meta-value">{{ $log->sprint_release ?? '—' }}</span>
                                    </div>
                                </div>
                                <div class="section-header">
                                    <i class="fas fa-lightbulb"></i> Context
                                </div>
                                <div class="section-content line-clamp-3 text-sm text-gray-700">
                                    <div class="text-expandable" id="context-{{ $log->id }}">
                                        {{ $log->context ?? 'No context provided.' }}
                                    </div>
                                    @if(strlen($log->context ?? '') > 200)
                                        <span class="expand-toggle" onclick="toggleExpand('context-{{ $log->id }}')" id="toggle-context-{{ $log->id }}">
                                            Show more
                                        </span>
                                    @endif
                                </div>
                                <div class="section-header">
                                    <i class="fas fa-check-circle"></i> Decision
                                </div>
                                <div class="section-content line-clamp-3 text-sm text-gray-700">
                                    <div class="text-expandable" id="decision-{{ $log->id }}">
                                        {{ $log->decision ?? 'No decision recorded.' }}
                                    </div>
                                    @if(strlen($log->decision ?? '') > 200)
                                        <span class="expand-toggle" onclick="toggleExpand('decision-{{ $log->id }}')" id="toggle-decision-{{ $log->id }}">
                                            Show more
                                        </span>
                                    @endif
                                </div>
                                <div class="section-header">
                                    <i class="fas fa-exclamation-triangle"></i> Impact / Risk
                                </div>
                                <div class="section-content line-clamp-3 text-sm text-gray-700">
                                    <div class="text-expandable" id="impact-{{ $log->id }}">
                                        {{ $log->impact_risk ?? 'No impact/risk information provided.' }}
                                    </div>
                                    @if(strlen($log->impact_risk ?? '') > 200)
                                        <span class="expand-toggle" onclick="toggleExpand('impact-{{ $log->id }}')" id="toggle-impact-{{ $log->id }}">
                                            Show more
                                        </span>
                                    @endif
                                </div>
                                @if($log->tags && count($log->tags) > 0)
                                <div class="tags-container">
                                    @foreach($log->tags as $tag)
                                        <span class="tag" onclick="filterByTag('{{ $tag }}')">{{ $tag }}</span>
                                    @endforeach
                                </div>
                                @endif
                                @if($uniqueArtifacts && count($uniqueArtifacts) > 0)
                                <div class="artifacts-section">
                                    <h4 class="artifacts-title">Related Artifacts:</h4>
                                    @foreach($uniqueArtifacts as $attachment)
                                    <div class="artifact-item">
                                        @php
                                            $fileExt = pathinfo($attachment['name'], PATHINFO_EXTENSION);
                                            $fileSize = $attachment['size'] ?? 0;
                                            $iconMap = [
                                                'pdf' => 'fas fa-file-pdf',
                                                'doc' => 'fas fa-file-word',
                                                'docx' => 'fas fa-file-word',
                                                'xls' => 'fas fa-file-excel',
                                                'xlsx' => 'fas fa-file-excel',
                                                'jpg' => 'fas fa-file-image',
                                                'jpeg' => 'fas fa-file-image',
                                                'png' => 'fas fa-file-image',
                                                'gif' => 'fas fa-file-image',
                                                'txt' => 'fas fa-file-alt',
                                                'zip' => 'fas fa-file-archive',
                                                'rar' => 'fas fa-file-archive'
                                            ];
                                            $icon = $iconMap[strtolower($fileExt)] ?? 'fas fa-file';
                                            $sizeText = $fileSize > 0 ? number_format($fileSize / 1024, 1) . ' KB' : '0 bytes';
                                            $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        @endphp
                                        <div class="artifact-content">
                                            <span class="artifact-icon"><i class="{{ $icon }}"></i></span>
                                            <a href="{{ asset('storage/' . $attachment['path']) }}" target="_blank" class="artifact-link">
                                                {{ $attachment['name'] }}
                                            </a>
                                            <span class="artifact-size">({{ $sizeText }})</span>
                                            @if($isImage)
                                                <div class="artifact-thumbnail" data-src="{{ asset('storage/' . $attachment['path']) }}">
                                                    <i class="fas fa-eye"></i>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="empty-state text-center py-5">
                            <i class="bi bi-journal-check fs-1 text-muted"></i>
                            <h4 class="mt-3">No decision logs found</h4>
                            <p class="text-muted">Try adjusting your filters or add a new decision log.</p>
                        </div>
                    @endforelse
                    </div>
                    <!-- Pagination -->
                    <div class="pagination-container mt-4">
                        {{ $decisionLogs->withQueryString()->links('vendor.pagination.modern') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Decision Log Modal -->
    @include('docs._add_decision_log_modal')

    <!-- Edit Decision Log Modal -->
    @include('docs._edit_decision_log_modal')

@endsection

@section('footer')
    <script src="{{ asset_path('js/decision_logs.js') }}"></script>
@endsection 