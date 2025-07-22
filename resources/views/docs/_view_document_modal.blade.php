<!-- View Document Modal -->
<div class="modal fade" id="viewDocumentModal" tabindex="-1" aria-labelledby="viewDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDocumentModalLabel">View Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h4 id="viewDocumentTitle" class="text-primary"></h4>
                    <div class="text-muted small">
                        Category: <span id="viewDocumentCategory"></span> | 
                        Created: <span id="viewDocumentCreated"></span>
                        <span id="viewDocumentUpdated" style="display: none;"> | Updated: <span id="viewDocumentUpdatedDate"></span></span>
                    </div>
                </div>
                
                <div id="viewDocumentTags" class="mb-3" style="display: none;">
                    <strong>Tags:</strong>
                    <div id="viewDocumentTagsList"></div>
                </div>
                
                <div class="border rounded p-3 bg-light">
                    <div id="viewDocumentContent"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editDocumentBtn">
                    <i class="bi bi-pencil"></i> Edit Document
                </button>
            </div>
        </div>
    </div>
</div> 