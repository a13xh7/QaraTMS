@extends('layout.base_layout')

@section('content')

    @include('layout.sidebar_nav')


    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Documents
                <a class="mx-3" href="{{route("document_create_page", $project->id)}}">
                    <button type="button" class="btn btn-sm btn-primary"> <i class="bi bi-plus-lg"></i> Add New</button>
                </a>
            </h3>
        </div>

        <div class="row m-0 mb-3" style="min-height: 700px">

            @if(isset($selectedDocument))
                <div class="col base_block shadow">

                    <div class="mt-2 d-flex justify-content-between border-bottom pb-1">
                        <div>
                            <span class="fs-2">{{$selectedDocument->title}}</span>
                        </div>

                        <div class="mt-2">
                            <a href="{{route('document_edit_page', [$selectedDocument->project_id, $selectedDocument->id])}}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>


                            <form method="POST" action="{{route("document_delete")}}" style="display: inline-block">
                                @csrf
                                <input type="hidden" name="id" value="{{$selectedDocument->id}}">

                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>


                    </div>

                    <div class="mt-2">
                        {!! $selectedDocument->content !!}
                    </div>
                </div>
            @endif

            <div class="col base_block shadow ps-3 pe-3 ms-2"
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

        </div>

    </div>

@endsection


