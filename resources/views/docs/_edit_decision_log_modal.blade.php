<div class="modal fade" id="editDecisionLogModal" tabindex="-1" aria-labelledby="editDecisionLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDecisionLogModalLabel">Edit Decision Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editDecisionLogForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_id" name="id">
                    
                    <div class="mb-3">
                        <label for="edit_title" class="form-label">Decision Title *</label>
                        <input type="text" class="form-control" id="edit_title" name="title" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_decision_type" class="form-label">Decision Type</label>
                            <select name="decision_type" id="edit_decision_type" class="form-control" required>
                                <option value="">Select Type</option>
                                @foreach($allTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_decision_owner" class="form-label">Decision Owner</label>
                            <input type="text" class="form-control" id="edit_decision_owner" name="decision_owner" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_involved_qa" class="form-label">Involved QA</label>
                        <input type="text" class="form-control" id="edit_involved_qa" name="involved_qa" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_decision_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="edit_decision_date" name="decision_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_sprint_release" class="form-label">Sprint/Release</label>
                            <input type="text" class="form-control" id="edit_sprint_release" name="sprint_release">
                            <small class="form-text text-muted">E.g., Sprint-18, Release-2.12</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_context" class="form-label">Context</label>
                        <textarea class="form-control" id="edit_context" name="context" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_decision" class="form-label">Decision Summary</label>
                        <textarea class="form-control" id="edit_decision" name="decision" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="edit_impact_risk" class="form-label">Impact / Risk</label>
                        <textarea class="form-control" id="edit_impact_risk" name="impact_risk" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status" required>
                                <option value="Draft">Draft</option>
                                <option value="Finalized">✅ Finalized</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_tags" class="form-label">Tags (comma-separated)</label>
                            <input type="text" class="form-control" id="edit_tags" name="tags">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_related_artifacts" class="form-label">Upload New Artifacts</label>
                        <input type="file" class="form-control" id="edit_related_artifacts" name="related_artifacts[]" multiple>
                        <div id="current_artifacts" class="mt-2"></div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 