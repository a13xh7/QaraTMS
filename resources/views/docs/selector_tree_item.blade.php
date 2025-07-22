@php
    $depth = $document->ancestors()->count();
    $prefix = str_repeat('-', $depth);
    $isCurrentDoc = isset($selectedDocument) && $document->id == $selectedDocument->id;
    $isParentDoc = isset($selectedDocument) && $document->id == $selectedDocument->parent_id;
    $paddingLeft = $depth * 20;
    $hasChildren = $document->children->count() > 0;
@endphp

@if(!$isCurrentDoc)
    <option value="{{ $document->id }}" 
            {{ isset($selected) && $selected ? 'selected' : '' }}
            style="padding-left: {{ $paddingLeft }}px">
        {{ $prefix }} {{ $document->title }}
    </option>

    @if($hasChildren)
        @foreach($document->children as $childDocument)
            @include('docs.selector_tree_item', [
                'document' => $childDocument,
                'selected' => isset($selectedDocument) && $selectedDocument->parent_id == $childDocument->id
            ])
        @endforeach
    @endif
@endif

