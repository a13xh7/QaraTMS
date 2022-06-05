<div>
    <a class="link-dark docs_tree_link" href="{{route('document_show_page', [$document->project_id, $document->id])}}">
        <i class="bi bi-file-earmark-text"></i> {{$document->title}}
    </a>

    @foreach($document->children as $document)
        <div style="margin-left: 10px;">
            @include('docs.tree_item')
        </div>
    @endforeach
</div>
