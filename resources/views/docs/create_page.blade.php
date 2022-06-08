@extends('layout.base_layout')

@section('head')
    <link href="{{asset('editor/summernote-lite.min.css')}}" rel="stylesheet">
    <script src="{{asset('editor/summernote-lite.min.js')}}"></script>
@endsection

@section('content')

    @include('layout.sidebar_nav')

    <div class="col fh">

        <div class="border-bottom my-3">
            <h3 class="page_title">
                Add Document
            </h3>
        </div>


        <div class="base-block shadow p-4">
            <form action="{{route('document_create')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="project_id" value="{{$project->id}}">

                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" required maxlength="255">
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


                <!-- This container will become the editable. -->
                <textarea id="content" name="content"></textarea>

                <button type="submit" id="submit_btn" class="btn btn-success px-5 mt-3">
                    <b>Create</b>
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
        // $('#content').summernote({
        //     minHeight: '300px',
        //     callbacks: {
        //         onImageUpload: function(files, editor, welEditable) {
        //             sendFile(files[0], editor, welEditable);
        //         }
        //     }
        // });

        $('#content').summernote({
            minHeight: '300px'
        });



        {{--function sendFile(file, editor, welEditable) {--}}
        {{--    var lib_url = '{{route('ckeditor.upload')}}';--}}
        {{--    data = new FormData();--}}
        {{--    data.append("file", file);--}}
        {{--    console.log(data)--}}
        {{--    $.ajax({--}}
        {{--        data: data,--}}
        {{--        type: "POST",--}}
        {{--        url: lib_url,--}}
        {{--        cache: false,--}}
        {{--        processData: false,--}}
        {{--        contentType: false,--}}
        {{--        success: function(url) {--}}
        {{--            console.log(url)--}}
        {{--            var image = $('<img>').attr('src', url);--}}
        {{--            $('.summernote_editor').summernote("insertNode", image[0]);--}}
        {{--        }--}}
        {{--    });--}}
        {{--}--}}
    </script>

@endsection
