@extends('layout.base_layout')

@section('head')
    <script src="https://cdn.ckeditor.com/ckeditor5/34.0.0/decoupled-document/ckeditor.js"></script>
@endsection

@section('content')

    @include('layout.sidebar_nav')

    <div class="col">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Edit Document  <i class="bi bi-arrow-right"></i> {{$selectedDocument->title}}
            </h3>
        </div>


        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.<br><br>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card p-4">
            <form action="{{route('document_update')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{$selectedDocument->id}}">
                <input type="hidden" name="project_id" value="{{$selectedDocument->id}}">
                <input type="hidden" name="content" id="content" value="">

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" value="{{$selectedDocument->title}}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Parent</label>

                    <select class="form-select" name="parent_id" id="parent_id">

                        <option value="" selected>-- Root --</option>

                        @foreach($documents as $document)
                            @include('docs.selector_tree_item')
                        @endforeach

                    </select>
                </div>



                <!-- The toolbar will be rendered in this container. -->
                <div id="toolbar-container"></div>

                <!-- This container will become the editable. -->
                <div id="editor">{!! $selectedDocument->content !!}</div>

                <button type="submit" id="submit_btn" class="btn btn-warning px-5 mt-3">
                    <b>Update</b>
                </button>

                <a href=" {{ url()->previous() }}" class="btn btn-outline-dark px-5 mt-3">
                    <b>Cancel</b>
                </a>


            </form>
        </div>

    </div>



@endsection


@section('footer')
    <script>
        let theEditor;

        DecoupledEditor
            .create( document.querySelector( '#editor' ), {
                ckfinder: {
                    uploadUrl: '{{route('ckeditor.upload').'?_token='.csrf_token()}}',
                }
            })
            .then( editor => {
                const toolbarContainer = document.querySelector( '#toolbar-container' );
                toolbarContainer.appendChild( editor.ui.view.toolbar.element );
                theEditor = editor;
            })
            .catch( error => {
                console.error( error );
            });


        $( document ).ready(function() {
            $('body').on('click', '#submit_btn', function () {
                $('#content').val(theEditor.getData());
            });
        });
    </script>
@endsection
