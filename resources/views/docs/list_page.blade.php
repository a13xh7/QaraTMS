@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')


    <div class="col fh document">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Documents

                @can('add_edit_documents')
                    <a class="mx-3" href="{{route("document_create_page", $project->id)}}">
                        <button type="button" class="btn btn-sm btn-primary"> <i class="bi bi-plus-lg"></i> Add New</button>
                    </a>
                @endcan
            </h3>
        </div>

        <div class="row m-0 mb-3 shadow" style="min-height: 700px">

            <div class="col base_block pe-3 border"
                 @if( isset($selectedDocument) )
                 style="max-width: 300px; background: #00000005"
                @endif >

                <div class="my-2 border-bottom">
                    <span class="fs-4">Table of Contents</span>
                </div>


                <div >
                    @foreach($documents as $document)
                        @include('docs.tree_item')
                    @endforeach
                </div>

            </div>


            @if(isset($selectedDocument))
                <div class="col base_block border">

                    <div class="mt-2 d-flex justify-content-between border-bottom pb-1">
                        <div class="doc_title" title="{{$selectedDocument->title}}">
                            <span class="fs-3">{{$selectedDocument->title}}</span>
                        </div>

                        <div class="mt-2">
                            @can('add_edit_documents')
                                <a href="{{route('document_edit_page', [$selectedDocument->project_id, $selectedDocument->id])}}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endcan


                            @can('delete_documents')
                                <form method="POST" action="{{route("document_delete")}}" style="display: inline-block">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$selectedDocument->id}}">

                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            @endcan
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


