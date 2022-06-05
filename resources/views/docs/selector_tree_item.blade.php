
@if(isset($selectedDocument->id))

    @if($document->id != $selectedDocument->id)

        <option value="{{$document->id}}"
            @if($document->id == $selectedDocument->parent_id)
                selected
            @endif
        >

            @for ($i = 0; $i < $document->ancestors()->count(); $i++)
                -
            @endfor

            {{$document->title}}

        </option>

        @foreach($document->children as $document)
            @include('docs.selector_tree_item')
        @endforeach
    @endif

@else

    <option value="{{$document->id}}">

        @for ($i = 0; $i < $document->ancestors()->count(); $i++)
            -
        @endfor
        {{$document->title}}
    </option>

    @foreach($document->children as $document)
        @include('docs.selector_tree_item')
    @endforeach
@endif

