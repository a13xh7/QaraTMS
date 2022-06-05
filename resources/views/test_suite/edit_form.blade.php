<form action="{{route('test_suite_update')}}" method="POST">

    <input name="id" id="tse_id" type="hidden" value="{{$editableSuite->id}}">
    <input name="repository_id" id="tse_repository_id" type="hidden" value="{{$repository->id}}">
    <input name="project_id" type="hidden" value="{{$repository->project_id}}">

    <div class="mb-2">
        <label for="tse_title" class="form-label">Name</label>
        <input id="tse_title" name="title" type="text" class="form-control" value="{{$editableSuite->title}}" placeholder="Name" required>
    </div>

    <div class="mb-2">
        <label for="tse_parent_id" class="form-label">Move to</label>

        <select name="parent_id" id="tse_parent_id" class="form-select">

            <option value="" selected>-- Root --</option>

            @foreach($suitesTree as $suite)
                @include('test_suite.selector_tree_item')
            @endforeach

        </select>

    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-danger me-2" data-bs-dismiss="modal" onclick="closeTestSuiteEditor()">Cancel</button>
        <button type="submit" class="btn btn-warning">Update</button>
    </div>

</form>
