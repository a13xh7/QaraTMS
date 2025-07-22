@extends('layout.base_layout')

@section('head')
    <link href="{{ asset_path('css/docs.css') }}" rel="stylesheet">
@endsection

@section('content')

    @include('layout.sidebar_nav')


    <div class="col fh document">

        <div class="border-bottom my-3">
            <h3 class="border-bottom my-3 page-title">{{ $pageTitle ?? 'Documents' }}</h3>
            <div class="d-flex gap-3 mb-3">
                @if(isset($project))
                    @can('add_edit_documents')
                        <a href="{{route("document_create_page", $project->id)}}" class="text-decoration-none">
                            <button type="button" class="btn btn-primary px-3 text-nowrap">
                                + Add New Doc
                            </button>
                        </a>
                    @endcan
                @endif

                <input type="text" class="form-filter-control" id="documentFilter" placeholder="Filter by title...">
            </div>
        </div>

        <div class="row m-0 mb-3 shadow" style="min-height: 700px">

            <div class="col base_block pe-3 border toc-sidebar" @if(isset($selectedDocument))
            style="max-width: 300px; background: #f8f9fa" @endif>
                <div class="toc-header my-2 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fs-4">Table of Contents</span>
                    <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="toggleTOC()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="toc-content">
                    @if($documents->count() > 0)
                        @foreach($documents as $document)
                            @include('docs.tree_item')
                        @endforeach
                    @else
                        <div class="text-muted text-center py-4">
                            <i class="bi bi-file-earmark-text fs-4"></i>
                            <p class="mt-2">No documents yet</p>
                        </div>
                    @endif
                </div>
            </div>


            @if(isset($selectedDocument))
                <div class="col base_block border">

                    <div class="mt-2 d-flex justify-content-between border-bottom pb-1">
                        <div class="doc_title" title="{{$selectedDocument->title}}">
                            <span class="fs-3">{{$selectedDocument->title}}</span>
                        </div>

                        <div class="mt-2">
                            <div class="btn-group">
                                @can('add_edit_documents')
                                    <a href="{{route('document_edit_page', [$selectedDocument->project_id, $selectedDocument->id])}}"
                                        class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                @endcan

                                @can('delete_documents')
                                    <button type="button"
                                        class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split"
                                        data-bs-toggle="dropdown">
                                        <span class="visually-hidden">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <form method="POST" action="{{ route('document_delete') }}"
                                                id="deleteForm_{{ $selectedDocument->id }}" style="display: none;">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $selectedDocument->id }}">
                                            </form>
                                            <button type="button" class="dropdown-item text-danger"
                                                onclick="confirmDelete({{ $selectedDocument->id }})">
                                                <i class="bi bi-trash3"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                @endcan
                            </div>
                        </div>


                    </div>

                    <div class="mt-2">
                        {!! $selectedDocument->content !!}
                    </div>
                </div>
            @endif

        </div>

    </div>

@endsection

@section('footer')
    <script src="{{ asset_path('js/docs.js') }}"></script>
@endsection