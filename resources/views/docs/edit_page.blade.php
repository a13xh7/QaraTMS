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
    <link href="{{ asset_path('css/docs.css') }}" rel="stylesheet">
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
                                href="{{route('project_documents_list_page', $selectedDocument->project_id)}}">Documents</a>
                        </li>
                        <li class="breadcrumb-item active">Edit Document</li>
                    </ol>
                </nav>
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Back to Documents
                </a>
            </div>

            <div class="row">
                <!-- Main Form Column -->
                <div class="col-lg-9">
                    <form action="{{route('document_update')}}" method="POST" id="documentForm">
                        @csrf
                        <input type="hidden" name="id" value="{{$selectedDocument->id}}">
                        <input type="hidden" name="project_id" value="{{$selectedDocument->project_id}}">

                        <div class="form-group">
                            <label class="form-label">Document Title<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" value="{{$selectedDocument->title}}"
                                required>
                            <div class="help-text">Give your document a clear, descriptive name (max 255 characters)</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Content<span class="text-danger">*</span></label>
                            <textarea id="content" name="content"
                                class="form-control">{!! $selectedDocument->content !!}</textarea>
                        </div>

                        <div class="mt-4">
                            <button type="submit" id="submit_btn" class="btn-primary">
                                <i class="bi bi-check-lg"></i>
                                Update Document
                            </button>
                            <a href="{{ url()->previous() }}" class="btn-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-3">
                    <div class="info-section">
                        <div class="info-title">
                            <i class="bi bi-info-circle"></i>
                            About Documents
                        </div>
                        <div class="info-text">
                            Documents help you organize and store important information, procedures, and documentation for
                            your project.
                        </div>
                        <ul class="feature-list">
                            <li>
                                <i class="bi bi-pencil text-primary"></i>
                                <div>
                                    <div class="feature-title">Document Title</div>
                                    <div class="info-text">Give your document a clear, descriptive name</div>
                                </div>
                            </li>
                            <li>
                                <i class="bi bi-file-text text-primary"></i>
                                <div>
                                    <div class="feature-title">Content</div>
                                    <div class="info-text">Describe the content of your document</div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="info-section">
                        <div class="info-title">
                            <i class="bi bi-gear"></i>
                            Document Settings
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Parent Document</label>
                            <select class="form-select parent-select" name="parent_id" id="parent_id" form="documentForm">
                                <option value="" selected>-- Root Level --</option>
                                @foreach($documents as $document)
                                                            @include('docs.selector_tree_item', [
                                                                'selected' => $selectedDocument->parent_id == $document->id
                                                            ])
                                @endforeach
                            </select>
                            <div class="help-text">
                                <i class="bi bi-info-circle"></i>
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
