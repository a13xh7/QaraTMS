<form action="">

    <input name="project_id" id="tse_project_id" type="hidden" value="{{$repository->project_id}}">
    <input name="repository_id" id="tse_repository_id" type="hidden" value="{{$repository->id}}">
    <input name="parent_id" id="tse_parent_id" type="hidden" value="@if(isset($editableSuite)) {{$editableSuite->id}} @else  @endif">

    <div class="mb-2">
        <label for="tse_title" class="form-label">Name</label>
        <input id="tse_title" name="title" type="text" class="form-control" value="" placeholder="Name" required>
    </div>

<div class="d-flex justify-content-end">
    <button type="button" class="btn btn-danger me-2" data-bs-dismiss="modal" onclick="closeTestSuiteEditor()">Cancel</button>
    <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="createTestSuite()">Save</button>
</div>

</form>
