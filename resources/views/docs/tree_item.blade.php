<div class="document-tree-item">
    <a class="link-dark docs_tree_link {{ isset($selectedDocument) && $selectedDocument->id == $document->id ? 'active' : '' }}" 
       href="{{route('document_show_page', [$document->project_id, $document->id])}}"
       title="{{ $document->title }}">
        <i class="bi {{ $document->children->count() > 0 ? 'bi-folder' : 'bi-file-earmark-text' }}"></i>
        <span class="document-title">{{ $document->title }}</span>
    </a>

    @if($document->children->count() > 0)
        <div class="document-children" style="margin-left: 20px;">
            @foreach($document->children as $document)
                @include('docs.tree_item')
            @endforeach
        </div>
    @endif
</div>
