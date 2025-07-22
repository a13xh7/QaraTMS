<!-- Edit Document Modal -->
<div class="modal fade" id="editDocumentModal" tabindex="-1" aria-labelledby="editDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDocumentModalLabel">Edit Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDocumentForm" method="POST" action="">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_document_id" name="id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_document_title" class="form-label">Document Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_document_title" name="title" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_document_category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_document_category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="compliance">Compliance</option>
                            <option value="sop_qa">SOP & QA Docs</option>
                            <option value="test_exceptions">Test Exceptions</option>
                            <option value="audit_readiness">Audit Readiness</option>
                            <option value="knowledge_transfers">Knowledge Transfers</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="edit_document_parent" class="form-label">Parent Document</label>
                        <select class="form-select" id="edit_document_parent" name="parent_id">
                            <option value="">None (Root Level)</option>
                            @if(isset($documents) && $documents->count() > 0)
                                @foreach($documents as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->title }}</option>
                                    @if($doc->children && $doc->children->count() > 0)
                                        @include('docs._parent_options', ['documents' => $doc->children, 'level' => 1])
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        <small class="form-text text-muted">Optional: Select a parent document to create a hierarchical structure. Note: You cannot select the document itself or its children as parent.</small>
                    </div>

                    @php
                        $isAdmin = auth()->check() && (
                            strtolower(auth()->user()->role) === 'administrator' ||
                            strtolower(auth()->user()->role) === 'admin' ||
                        );
                    @endphp

                    <div class="mb-3">
                        <label for="edit_state" class="form-label">State <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_state" name="state" required {{ $isAdmin ? '' : 'disabled' }}>
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                        </select>
                        @if(!$isAdmin)
                            <input type="hidden" name="state" id="edit_state_hidden">
                        @endif
                    </div>

                    <div class="mb-3">
                        <label for="edit_reviewers" class="form-label">To be reviewed by</label>
                        <div id="edit_reviewers" class="form-check-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 4px; padding: 8px; background: #fafbfc;">
                            @foreach($users as $user)
                                <div class="form-check" style="margin-bottom: 6px; display: flex; align-items: center;">
                                    <input class="form-check-input" type="checkbox" name="reviewers[]" value="{{ $user->id }}" id="edit_reviewer_{{ $user->id }}" style="margin-right: 8px; margin-top: 0;">
                                    <label class="form-check-label" for="edit_reviewer_{{ $user->id }}" style="font-size: 0.97em; font-weight: 400; margin-bottom: 0;">
                                        {{ $user->name }} <span style="color: #888;">({{ $user->email }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="form-text text-muted">Select one or more users to review this document.</small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_document_content" class="form-label">Document Content <span class="text-danger">*</span></label>
                        <!-- Quill Editor Container -->
                        <div id="editQuillEditor" style="height: 400px;"></div>
                        <input type="hidden" name="content" id="edit_document_content" required>
                        <small class="form-text text-muted">Use the toolbar above to format your content with bold, italic, lists, tables, and more.</small>
                    </div>

                    <div class="mb-3">
                        <label for="edit_document_tags" class="form-label">Tags</label>
                        <input type="text" class="form-control" id="edit_document_tags" name="tags" placeholder="Enter tags separated by commas">
                        <small class="form-text text-muted">Optional: Add tags to help categorize and search for this document.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Update Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let editQuill;
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Edit Quill ONCE
    editQuill = new Quill('#editQuillEditor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['blockquote', 'code-block'],
                ['link', 'image', 'clean']
            ]
        }
    });

    // Handle form submission
    $('#editDocumentForm').off('submit').on('submit', function() {
        var html = editQuill.root.innerHTML;
        document.getElementById('edit_document_content').value = html;
        // Let the browser handle submission and redirect
    });
});
</script> 