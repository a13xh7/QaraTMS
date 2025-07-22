@extends('layout.base_layout')

@section('head')
    <link href="{{ asset_path('css/docs.css') }}" rel="stylesheet">
    <style>
        /* Modern Documentation Page Styles */
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
            max-width: 500px;
            margin: 0 auto 3rem;
            position: relative;
            transition: max-width 0.3s ease;
        }

        /* When sidebar is hidden, allow wider search */
        .col.fh:not(.main-content-with-sidebar) .modern-search-container {
            max-width: 700px;
        }

        .modern-search-input {
            width: 100%;
            padding: 1rem 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            font-size: 1rem;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .modern-search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.1), 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .modern-search-input::placeholder {
            color: #94a3b8;
        }

        .modern-content-area {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
            padding: 0 1rem;
        }

        /* When sidebar is hidden, make documents grid use full width */
        .col.fh:not(.main-content-with-sidebar) .modern-documents-grid {
            margin-left: -1rem;
            margin-right: -1rem;
            padding: 0 2rem;
        }

        .modern-empty-card {
            background: white;
            border-radius: 20px;
            padding: 3rem 2rem;
            text-align: center;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 500px;
            width: 100%;
            border: 1px solid #f1f5f9;
        }

        .modern-empty-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
        }

        .modern-empty-icon i {
            font-size: 2rem;
            color: white;
        }

        .modern-empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }

        .modern-empty-subtitle {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .modern-add-button {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.2);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modern-add-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
            color: white;
            text-decoration: none;
        }

        .modern-add-button:active {
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modern-doc-title {
                font-size: 2rem;
            }
            
            .modern-doc-subtitle {
                font-size: 1rem;
            }
            
            .modern-empty-card {
                padding: 2rem 1.5rem;
            }
            
            .modern-empty-icon {
                width: 60px;
                height: 60px;
            }
            
            .modern-empty-icon i {
                font-size: 1.5rem;
            }
        }

        /* Animation for the empty state */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modern-empty-card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Hover effects for interactive elements */
        .modern-search-container:hover .modern-search-input {
            border-color: #cbd5e1;
        }

        .modern-empty-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.1), 0 10px 20px -5px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }

        /* Document Grid Styles */
        .modern-documents-grid {
            width: 100%;
            max-width: 1200px;
            transition: max-width 0.3s ease;
        }

        /* When sidebar is hidden, allow full width usage and better spacing */
        .col.fh:not(.main-content-with-sidebar) .modern-documents-grid {
            max-width: none;
            width: 100%;
        }

        .modern-documents-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }

        .modern-documents-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .modern-documents-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            padding: 0 1rem;
        }

        /* When sidebar is hidden, use better grid layout with improved spacing */
        .col.fh:not(.main-content-with-sidebar) .modern-documents-list {
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
            padding: 0 1rem;
        }

        .modern-document-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }

        .modern-document-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .modern-document-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .modern-document-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
            flex: 1;
            margin-right: 1rem;
        }

        .modern-document-actions {
            display: flex;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .modern-document-actions .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .modern-document-content {
            margin-bottom: 1rem;
        }

        .modern-document-excerpt {
            color: #64748b;
            line-height: 1.6;
            margin: 0;
        }

        .modern-document-full-content {
            color: #374151;
            line-height: 1.6;
            margin: 0;
        }

        /* Rich text content styling */
        .modern-document-full-content h1,
        .modern-document-full-content h2,
        .modern-document-full-content h3,
        .modern-document-full-content h4,
        .modern-document-full-content h5,
        .modern-document-full-content h6 {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #1e293b;
        }

        .modern-document-full-content h1 { font-size: 1.5rem; }
        .modern-document-full-content h2 { font-size: 1.25rem; }
        .modern-document-full-content h3 { font-size: 1.125rem; }
        .modern-document-full-content h4 { font-size: 1rem; }
        .modern-document-full-content h5 { font-size: 0.875rem; }
        .modern-document-full-content h6 { font-size: 0.75rem; }

        .modern-document-full-content p {
            margin-bottom: 0.75rem;
        }

        .modern-document-full-content ul,
        .modern-document-full-content ol {
            margin-bottom: 0.75rem;
            padding-left: 1.5rem;
        }

        .modern-document-full-content li {
            margin-bottom: 0.25rem;
        }

        .modern-document-full-content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }

        .modern-document-full-content table th,
        .modern-document-full-content table td {
            border: 1px solid #d1d5db;
            padding: 0.5rem;
            text-align: left;
        }

        .modern-document-full-content table th {
            background-color: #f9fafb;
            font-weight: 600;
        }

        .modern-document-full-content strong,
        .modern-document-full-content b {
            font-weight: 600;
            color: #1e293b;
        }

        .modern-document-full-content em,
        .modern-document-full-content i {
            font-style: italic;
        }

        .modern-document-full-content code {
            background-color: #f3f4f6;
            padding: 0.125rem 0.25rem;
            border-radius: 0.25rem;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.875em;
        }

        .modern-document-full-content blockquote {
            border-left: 4px solid #3b82f6;
            padding-left: 1rem;
            margin: 1rem 0;
            font-style: italic;
            color: #6b7280;
        }

        .modern-document-full-content a {
            color: #3b82f6;
            text-decoration: none;
        }

        .modern-document-full-content a:hover {
            text-decoration: underline;
        }

        .modern-document-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .modern-tag {
            background: #e0e7ff;
            color: #3730a3;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .modern-document-meta {
            border-top: 1px solid #f1f5f9;
            padding-top: 1rem;
        }

        /* Responsive adjustments for document grid */
        @media (max-width: 768px) {
            .modern-documents-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .modern-documents-list {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .modern-document-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .modern-document-actions {
                justify-content: flex-start;
            }
        }

        /* View modal content styling */
        #viewDocumentContent {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
        }

        #viewDocumentContent h1,
        #viewDocumentContent h2,
        #viewDocumentContent h3,
        #viewDocumentContent h4,
        #viewDocumentContent h5,
        #viewDocumentContent h6 {
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: #1e293b;
        }

        #viewDocumentContent h1 { font-size: 1.75rem; }
        #viewDocumentContent h2 { font-size: 1.5rem; }
        #viewDocumentContent h3 { font-size: 1.25rem; }
        #viewDocumentContent h4 { font-size: 1.125rem; }
        #viewDocumentContent h5 { font-size: 1rem; }
        #viewDocumentContent h6 { font-size: 0.875rem; }

        #viewDocumentContent p {
            margin-bottom: 1rem;
        }

        #viewDocumentContent ul,
        #viewDocumentContent ol {
            margin-bottom: 1rem;
            padding-left: 2rem;
        }

        #viewDocumentContent li {
            margin-bottom: 0.5rem;
        }

        #viewDocumentContent table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }

        #viewDocumentContent table th,
        #viewDocumentContent table td {
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            text-align: left;
        }

        #viewDocumentContent table th {
            background-color: #f9fafb;
            font-weight: 600;
        }

        #viewDocumentContent strong,
        #viewDocumentContent b {
            font-weight: 600;
            color: #1e293b;
        }

        #viewDocumentContent em,
        #viewDocumentContent i {
            font-style: italic;
        }

        #viewDocumentContent code {
            background-color: #f3f4f6;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.875em;
            color: #dc2626;
        }

        #viewDocumentContent blockquote {
            border-left: 4px solid #3b82f6;
            padding-left: 1.5rem;
            margin: 1.5rem 0;
            font-style: italic;
            color: #6b7280;
            background-color: #f8fafc;
            padding: 1rem 1.5rem;
            border-radius: 0.375rem;
        }

        #viewDocumentContent a {
            color: #3b82f6;
            text-decoration: none;
        }

        #viewDocumentContent a:hover {
            text-decoration: underline;
        }

        /* Content Table Sidebar Styles */
        .content-table-sidebar {
            position: fixed;
            left: 200px; /* Position after the main navigation sidebar (200px width) */
            top: 60px; /* Adjust based on your header height */
            width: 280px; /* Slightly smaller to give more room */
            height: calc(100vh - 60px);
            background: white;
            border-right: 1px solid #e2e8f0;
            overflow-y: auto;
            z-index: 1000;
            padding: 1rem;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .content-table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .content-table-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .content-table-search {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }

        .content-table-search:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .content-table {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .content-table ul {
            list-style: none;
            padding-left: 1.5rem;
            margin: 0;
        }

        .content-table li {
            margin: 0;
            position: relative;
        }

        .content-table-item {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            color: #374151;
            text-decoration: none;
        }

        .content-table-item:hover {
            background-color: #f3f4f6;
            color: #1e293b;
            text-decoration: none;
        }

        .content-table-item.active {
            background-color: #dbeafe;
            color: #1e40af;
            font-weight: 500;
        }

        .content-table-icon {
            margin-right: 0.5rem;
            font-size: 0.875rem;
            width: 16px;
            text-align: center;
        }

        .content-table-toggle {
            margin-right: 0.25rem;
            cursor: pointer;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .content-table-toggle:hover {
            color: #374151;
        }

        .content-table-item.has-children > .content-table-toggle {
            visibility: visible;
        }

        .content-table-item:not(.has-children) > .content-table-toggle {
            visibility: hidden;
        }

        .content-table-children {
            display: none;
        }

        .content-table-children.expanded {
            display: block;
        }

        .drag-handle {
            margin-left: auto;
            color: #9ca3af;
            cursor: grab;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .content-table-item:hover .drag-handle {
            opacity: 1;
        }

        .drag-handle:active {
            cursor: grabbing;
        }

        /* Default margin for main content to account for main navigation */
        .col.fh {
            margin-left: 205px; /* Account for main navigation (200px) + minimal padding */
            transition: margin-left 0.3s ease;
        }

        /* Adjust main content when sidebar is visible */
        .main-content-with-sidebar {
            margin-left: 500px; /* 200px (main nav) + 280px (content table) + 20px padding */
        }

        /* When sidebar is hidden, ensure minimal margin for attachment to side */
        .col.fh:not(.main-content-with-sidebar) {
            margin-left: 0; /* No margin when sidebar is hidden */
        }

        /* State badges in content table */
        .content-table-state {
            margin-left: auto;
            margin-right: 0.5rem;
            font-size: 0.625rem;
            padding: 0.125rem 0.375rem;
            border-radius: 9999px;
        }

        .content-table-state.draft {
            background-color: #fef3c7;
            color: #92400e;
        }

        .content-table-state.approved {
            background-color: #d1fae5;
            color: #065f46;
        }

        /* Toggle button for sidebar - now inline with title */
        .sidebar-toggle {
            transition: all 0.3s ease;
        }

        .sidebar-toggle:hover {
            background-color: #f8f9fa;
            border-color: #adb5bd;
        }

        .sidebar-toggle i {
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .content-table-sidebar {
                width: 280px;
                left: 0; /* On mobile, start from left edge */
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .content-table-sidebar.mobile-open {
                transform: translateX(0);
            }

            .main-content-with-sidebar {
                margin-left: 0;
            }

            .col.fh {
                margin-left: 0; /* Reset margin on mobile */
            }
        }

        /* SortableJS drag and drop styles */
        .sortable-ghost {
            opacity: 0.4;
            background-color: #f3f4f6;
        }

        .sortable-chosen {
            background-color: #dbeafe;
        }

        .sortable-drag {
            background-color: #bfdbfe;
            transform: rotate(5deg);
        }

        /* Parent selection styling */
        select option:disabled {
            color: #ccc;
            font-style: italic;
        }

        select option[disabled] {
            background-color: #f8f9fa;
        }

        #advancedSearchFilters {
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            #advancedSearchFilters {
                flex-direction: column !important;
                align-items: stretch !important;
                max-width: 100% !important;
            }
            .modern-search-container {
                flex-direction: column !important;
                gap: 0.5rem !important;
                max-width: 100% !important;
            }
        }

        .modern-filter-bar {
            background: transparent !important;
            box-shadow: none !important;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            margin: 0 auto 2rem auto;
            max-width: 1200px;
            overflow-x: auto;
        }
        .modern-filter-bar .form-select,
        .modern-filter-bar .form-control {
            min-width: 120px;
            height: 38px;
            font-size: 1rem;
            border-radius: 6px;
        }
        @media (max-width: 768px) {
            .modern-filter-bar {
                flex-direction: column;
                gap: 0.5rem;
                padding: 0.5rem;
                max-width: 100%;
            }
            .modern-filter-bar .form-select,
            .modern-filter-bar .form-control {
                width: 100% !important;
                min-width: 0;
            }
        }

        .long-link {
            word-break: break-all;
            overflow-wrap: anywhere;
        }
    </style>
@endsection

@section('content')
    @include('layout.sidebar_nav')

    <!-- Content Table Sidebar -->
    <div class="content-table-sidebar" id="contentTableSidebar">
        <div class="content-table-header">
            <h3 class="content-table-title">
                <i class="bi bi-file-text me-2"></i>
                Content
            </h3>
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-primary" onclick="handleAddDocument()" title="Add Child Document">
                    <i class="bi bi-plus"></i>
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="handleAddRootDocument()" title="Add Root Level Document">
                    <i class="bi bi-plus-square"></i>
                </button>
            </div>
        </div>
        
        <input 
            type="text" 
            class="content-table-search" 
            placeholder="Search by title"
            id="contentTableSearch"
        >
        
        <ul class="content-table" id="contentTable">
            @if($documents && $documents->count() > 0)
                @php
                    $rootDocuments = $documents->filter(function($doc) {
                        return is_null($doc->parent_id);
                    })->sortBy('position');
                @endphp
                @foreach($rootDocuments as $document)
                    @include('docs._content_table_item', ['document' => $document, 'allDocuments' => $documents])
                @endforeach
            @else
                <li class="text-muted text-center py-3">
                    <i class="bi bi-file-text-fill"></i><br>
                    No documents yet
                </li>
            @endif
        </ul>
    </div>

    <div class="col fh" id="mainContent">
        <div class="modern-doc-container">
            <div class="modern-doc-header">
                <div class="d-flex align-items-center justify-content-center gap-3 mb-2 flex-wrap">
                    <h1 class="modern-doc-title mb-0">{{ $pageTitle ?? 'Menu' }}</h1>
                    <!-- Sidebar Toggle Button -->
                    <button class="sidebar-toggle btn btn-outline-secondary btn-sm" onclick="toggleContentSidebar()" title="Toggle Content Table">
                        <i class="bi bi-layout-sidebar-inset"></i>
                    </button>
                </div>
                <p class="modern-doc-subtitle">
                    @switch($pageTitle)
                        @case('Compliance')
                            Ensure adherence to standards, regulations, and internal policies.
                            @break
                        @case('SOP & QA Docs')
                            Central repository for Standard Operating Procedures and QA documentation.
                            @break
                        @case('Test Exceptions')
                            Log and explain deviations or unexpected outcomes during testing.
                            @break
                        @case('Audit Readiness')
                            Prepare and organize key evidence for smooth audit processes.
                            @break
                        @case('Knowledge Transfers')
                            Share critical information and processes to support smooth handovers.
                            @break
                        @default
                            Help me fill this what better for that
                    @endswitch
                </p>
                <!-- Simple Search Bar -->
                <div class="modern-search-container d-flex align-items-center gap-2" style="max-width: 700px; margin: 0 auto 1.5rem;">
                    <input 
                        type="text" 
                        class="modern-search-input" 
                        placeholder="Search or filter documents..."
                        id="documentSearch"
                        style="min-width:0;"
                    >
                </div>
            </div>

            <!-- Unified Advanced Filter Bar -->
            <div class="modern-filter-bar d-flex flex-wrap align-items-center gap-2 mb-4 justify-content-center" style="border-radius: 12px; padding: 0.75rem 1rem; max-width: 1200px; margin: 0 auto 2rem; background: transparent;">
                <select id="filterState" class="form-select form-select-sm" style="width: 140px;">
                    <option value="">All States</option>
                    <option value="approved">Approved</option>
                    <option value="draft">Draft</option>
                </select>
                <input type="text" id="filterTags" class="form-control form-control-sm" placeholder="Tags (comma separated)" style="width: 180px;">
                <input type="date" id="filterDateStart" class="form-control form-control-sm" style="width: 150px;">
                <input type="date" id="filterDateEnd" class="form-control form-control-sm" style="width: 150px;">
                <select id="filterUser" class="form-select form-select-sm" style="width: 200px;">
                    <option value="">Edited or created by</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <select id="filterUpdated" class="form-select form-select-sm" style="width: 180px;">
                    <option value="">Last updated: Anytime</option>
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                </select>
                <button id="applyFiltersBtn" class="btn btn-primary btn-sm" type="button">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>

            <div class="modern-content-area">
                @if($documents && $documents->count() > 0)
                    <div class="modern-documents-grid">
                        <div class="modern-documents-header">
                            <h3 class="modern-documents-title">{{ $documents->count() }} Document{{ $documents->count() > 1 ? 's' : '' }}</h3>
                            <a href="#" class="modern-add-button" onclick="handleAddDocument()">
                                <i class="bi bi-plus-lg"></i>
                                + Add Document
                            </a>
                        </div>
                        
                        <div class="modern-documents-list">
                            @foreach($documents as $document)
                                @php $canApprove = auth()->check() && (
                                    $document->reviewers->contains(auth()->id()) ||
                                    in_array(strtolower(auth()->user()->role), ['administrator', 'admin'])
                                ); @endphp
                                <div class="modern-document-card" data-document-id="{{ $document->id }}" data-author-id="{{ $document->author_id }}">
                                    <div class="modern-document-header">
                                        <h4 class="modern-document-title mb-0">{{ $document->title }}</h4>
                                    </div>
                                    <div class="modern-document-content mt-2">
                                        <div class="modern-document-excerpt long-link">
                                            @php
                                                $content = strip_tags($document->content);
                                                $isUrl = filter_var($content, FILTER_VALIDATE_URL);
                                            @endphp
                                            @if($isUrl)
                                                <a href="{{ $content }}" target="_blank" rel="noopener noreferrer" class="long-link">{{ Str::limit($content, 200) }}</a>
                                            @else
                                                {{ Str::limit($content, 200) }}
                                            @endif
                                        </div>
                                        @if(strlen(strip_tags($document->content)) > 200)
                                            <div class="modern-document-full-content long-link" style="display: none;">
                                                @if($isUrl)
                                                    <a href="{{ $content }}" target="_blank" rel="noopener noreferrer" class="long-link">{{ $content }}</a>
                                                @else
                                                    {!! $document->content !!}
                                                @endif
                                            </div>
                                            <button class="btn btn-sm btn-link p-0 mt-2" onclick="toggleFullContent(this)">
                                                Show more
                                            </button>
                                        @endif
                                    </div>
                                    @if($document->tags && count($document->tags))
                                        <div class="modern-document-tags mb-2">
                                            @foreach($document->tags as $tag)
                                                <span class="modern-tag">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="modern-document-meta mt-3">
                                        <small class="text-muted">
                                            State:
                                            <span class="badge {{ $document->state === 'approved' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($document->state) }}</span>
                                            | Created: {{ $document->created_at->format('M d, Y') }}
                                            @if($document->updated_at != $document->created_at)
                                                | Updated: {{ $document->updated_at->format('M d, Y') }}
                                            @endif
                                            <br>
                                            Author: <span class="fw-bold">{{ $document->author ? $document->author->name : 'Unknown' }}</span>
                                        </small>
                                    </div>
                                    @if($document->state === 'draft' && $canApprove)
                                        <div class="d-flex justify-content-end mt-3">
                                            <form method="POST" action="{{ route('documents.approve', $document->id) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-success">Approve</button>
                                            </form>
                                        </div>
                                    @endif
                                    <div class="modern-document-actions d-flex justify-content-end align-items-center gap-2 mt-3 pt-3 border-top">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewDocument({{ $document->id }})">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="editDocument({{ $document->id }})">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteDocument({{ $document->id }})">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="modern-empty-card">
                        <div class="modern-empty-icon">
                            @switch($pageTitle)
                                @case('Compliance')
                                    <i class="bi bi-shield-check"></i>
                                    @break
                                @case('SOP & QA Docs')
                                    <i class="bi bi-file-earmark-text"></i>
                                    @break
                                @case('Test Exceptions')
                                    <i class="bi bi-exclamation-diamond"></i>
                                    @break
                                @case('Audit Readiness')
                                    <i class="bi bi-bar-chart"></i>
                                    @break
                                @case('Knowledge Transfers')
                                    <i class="bi bi-arrow-repeat"></i>
                                    @break
                                @default
                                    <i class="bi bi-journal-text"></i>
                            @endswitch
                        </div>
                        
                        <h3 class="modern-empty-title">No documents yet</h3>
                        <p class="modern-empty-subtitle">Start by adding a new {{ $pageTitle ?? 'Menu' }} document</p>
                        
                        <a href="#" class="modern-add-button" onclick="handleAddDocument()">
                            <i class="bi bi-plus-lg"></i>
                            + Add Document
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('footer')
    @include('docs._add_document_modal')
    @include('docs._view_document_modal')
    @include('docs._edit_document_modal')
    <script>window.pageTitle = @json($pageTitle ?? 'Menu');</script>
    <script src="{{ asset('js/modern_documentation_page.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endsection 