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
                Edit Document
            </h3>
        </div>

        <div class="base-block shadow p-4 ">
            <form action="{{route('document_update')}}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" value="{{$selectedDocument->id}}">
                <input type="hidden" name="project_id" value="{{$selectedDocument->id}}">

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


                <!-- This container will become the editable. -->
                <textarea id="content" name="content">{!! $selectedDocument->content !!}</textarea>


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
        $( document ).ready(function() {

            $('#content').summernote({
                minHeight: '300px',
            });

        });
    </script>
@endsection
