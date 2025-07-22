<div class="modal fade" id="addDecisionLogModal" tabindex="-1" aria-labelledby="addDecisionLogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDecisionLogModalLabel">Add New Decision Log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form action="{{ route('documents.decision_logs.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="title" class="form-label">Decision Title *</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="decision_type" class="form-label">Decision Type *</label>
                            <select name="decision_type" id="add_decision_type" class="form-control" required>
                                <option value="">Select Type</option>
                                @foreach($allTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="decision_owner" class="form-label">Decision Owner *</label>
                            <input type="text" class="form-control" id="decision_owner" name="decision_owner" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="involved_qa" class="form-label">Involved QA *</label>
                        <input type="text" class="form-control" id="involved_qa" name="involved_qa" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="decision_date" class="form-label">Decision Date *</label>
                            <input type="date" class="form-control" id="decision_date" name="decision_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sprint_release" class="form-label">Sprint/Release</label>
                            <input type="text" class="form-control" id="sprint_release" name="sprint_release">
                            <small class="form-text text-muted">E.g., Sprint-18, Release-2.12</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="context" class="form-label">Context *</label>
                        <textarea class="form-control" id="context" name="context" rows="3" required placeholder="Describe the context and background of this decision..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="decision" class="form-label">Decision Summary *</label>
                        <textarea class="form-control" id="decision" name="decision" rows="3" required placeholder="What was the final decision made?"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="impact_risk" class="form-label">Impact / Risk *</label>
                        <textarea class="form-control" id="impact_risk" name="impact_risk" rows="3" required placeholder="What are the potential impacts and risks of this decision?"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">Select Status</option>
                                <option value="Draft">Draft</option>
                                <option value="Finalized">Finalized</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tags" class="form-label">Tags (comma-separated)</label>
                            <input type="text" class="form-control" id="tags" name="tags" placeholder="priority, feature, scope">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="related_artifacts" class="form-label">Upload Related Artifacts</label>
                        <input type="file" class="form-control" id="related_artifacts" name="related_artifacts[]" multiple>
                        <small class="form-text text-muted">Supported formats: JPG, PNG, PDF, DOC, DOCX (max 2MB each)</small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Decision Log</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 