@foreach($documents as $doc)
    <option value="{{ $doc->id }}">{{ str_repeat('— ', $level) }}{{ $doc->title }}</option>
    @if($doc->children && $doc->children->count() > 0)
        @include('docs._parent_options', ['documents' => $doc->children, 'level' => $level + 1])
    @endif
@endforeach 