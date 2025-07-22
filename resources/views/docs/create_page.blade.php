@extends('layout.base_layout')

@section('head')
    <!-- jQuery first -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap (required by Summernote) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Summernote CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset_path('css/docs.css') }}">
@endsection

@section('content')
    <div class="d-flex">
        @include('layout.sidebar_nav')

        <div class="main-content flex-grow-1">
            <!-- Breadcrumb -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{route('project_list_page')}}">Projects</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{route('project_documents_list_page', $project->id)}}">Documents</a></li>
                        <li class="breadcrumb-item active">Create New</li>
                    </ol>
                </nav>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Documents
                </a>
            </div>

            <div class="row g-4">
                <!-- Main Form Column -->
                <div class="col-lg-9">
                    <div class="editor-container p-4">
                        <form action="{{route('document_create')}}" method="POST" id="documentForm">
                            @csrf
                            <input type="hidden" name="project_id" value="{{$project->id}}">

                            <div class="mb-4">
                                <label for="title" class="form-label">Document Title<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control title-input @error('title') is-invalid @enderror"
                                    name="title" maxlength="255" novalidate
                                    placeholder="Enter a descriptive name for this document">
                                <div class="help-text">Give your document a clear, descriptive name (max 255 characters)
                                </div>
                                @error('title')
                                    <div class="validation-message">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="content" class="form-label">Content<span class="text-danger">*</span></label>
                                <textarea id="content" name="content"
                                    class="content-textarea @error('content') is-invalid @enderror"
                                    placeholder="Describe the content of your document here..."></textarea>
                                @error('content')
                                    <div class="validation-message">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mt-4">
                                <button type="submit" id="submit_btn" class="btn btn-primary px-4">
                                    <i class="bi bi-plus-lg me-2"></i>Create Document
                                </button>
                                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Sidebar -->
                <div class="col-lg-3">
                    <!-- About Documents -->
                    <div class="info-section">
                        <div class="info-title">
                            <i class="bi bi-info-circle"></i>
                            About Documents
                        </div>
                        <div class="info-text">
                            Documents help you organize and store important information, procedures, and documentation for
                            your project.
                        </div>
                        <ul class="feature-list mt-3">
                            <li>
                                <i class="bi bi-folder text-primary"></i>
                                <div>
                                    <div class="feature-title">Organization</div>
                                    <div class="info-text">Structure content hierarchically with parent-child relationship.s
                                    </div>
                                </div>
                            </li>
                            <li>
                                <i class="bi bi-clock-history text-primary"></i>
                                <div>
                                    <div class="feature-title">Document Title</div>
                                    <div class="info-text">This suppose to be Feature name.</div>
                                </div>
                            </li>
                            <li>
                                <i class="bi bi-share text-primary"></i>
                                <div>
                                    <div class="feature-title">Content</div>
                                    <div class="info-text">Describe the content of your document.</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Document Settings -->
                    <div class="info-section">
                        <div class="info-title">
                            <i class="bi bi-gear"></i>
                            Document Settings
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Parent Document</label>
                            <select class="form-select parent-select @error('parent_id') is-invalid @enderror"
                                name="parent_id" id="parent_id" form="documentForm">
                                <option value="" selected>-- Root Level --</option>
                                @foreach($documents as $document)
                                    @include('docs.selector_tree_item')
                                @endforeach
                            </select>
                            <div class="help-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Choose where this document appears in the hierarchy
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer')
    <script src="{{ asset_path('js/docs.js') }}"></script>
@endsection