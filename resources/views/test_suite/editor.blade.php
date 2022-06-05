<div id="test_suite_editor" class="modal fade"  data-bs-backdrop="static" data-bs-keyboard="false"
     tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="staticBackdropLabel">
                    @if($operation == 'create')
                        Add Test Suite
                    @elseif($operation == 'update')
                        Edit Test Suite
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                @if($operation == 'create')
                    @include('test_suite.create_form')
                @elseif($operation == 'update')
                    @include('test_suite.edit_form')
                @endif
            </div>

        </div>
    </div>
</div>
