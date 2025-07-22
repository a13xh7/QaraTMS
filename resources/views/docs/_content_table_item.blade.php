<li data-id="{{ $document->id }}" data-parent-id="{{ $document->parent_id }}">
    @php
        $children = $allDocuments->filter(function($doc) use ($document) {
            return $doc->parent_id == $document->id;
        })->sortBy('position');
        $hasChildren = $children->count() > 0;
    @endphp
    
    <div class="content-table-item {{ $hasChildren ? 'has-children' : '' }}" onclick="selectDocument({{ $document->id }})">
        @if($hasChildren)
            <span class="content-table-toggle" onclick="toggleChildren(event, this)">
                <i class="bi bi-chevron-right"></i>
            </span>
        @else
            <span class="content-table-toggle"></span>
        @endif
        
        <i class="content-table-icon bi bi-file-text"></i>
        
        <span class="content-table-text">{{ Str::limit($document->title, 25) }}</span>
        
        <span class="content-table-state {{ $document->state }}">{{ ucfirst($document->state) }}</span>
        
        <i class="drag-handle bi bi-grip-vertical" title="Drag to reorder"></i>
    </div>
    
    @if($hasChildren)
        <ul class="content-table-children">
            @foreach($children as $child)
                @include('docs._content_table_item', ['document' => $child, 'allDocuments' => $allDocuments])
            @endforeach
        </ul>
    @endif
</li> 