
let editor;
function renderEditors() {
    editor = $('.editor_textarea').summernote({
            minHeight: null,             // set minimum height of editor
            maxHeight: null,             // set maximum height of editor
            focus: true,                // set focus to editable area after initializing summernote

            toolbar: [
                ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['picture'],
            ]
        },
    );

    $('.editor_textarea').removeClass('editor_textarea');
}

/****************************************************************************
 * TEST CASE AREA in repository. Load TEST CASE forms
 ****************************************************************************/

let testCaseAreaLocator = "#test_case_area";

function loadTestCaseCreateForm() {
    if($('#tree li').length <= 0) {
        alert('Create at least one test suite.')
        return;
    }

    let url = activeTreeSuiteItem.getId() == null ? `/tc/create/${repository_id}` : `/tc/create/${repository_id}/${activeTreeSuiteItem.getId()}`;

    $(testCaseAreaLocator).load(url, function() {
        $('textarea').autoResize();
        collapseCasesList();
        renderEditors();
    });
}

function renderTestCase(test_case_id) {
    $(testCaseAreaLocator).load(`/tc/${test_case_id}`, function() {

        $(`.test_case`).removeClass("selected");
        $(`.test_case[data-case_id='${test_case_id}']`).addClass('selected');

        collapseCasesList();
    });
}

let oldParentId='';
function renderTestCaseEditForm(test_case_id) {
    $(testCaseAreaLocator).load(`/tc/${test_case_id}/edit`, function() {
        $('textarea').autoResize();
        oldParentId = $("#tccf_test_suite_select").val();
        collapseCasesList();
        renderEditors();
    });
}

function closeTestCaseEditor() {
    $('#test_case_editor').remove();
    expandCasesList();
}

function isTestCaseCreateOrEditFormLoaded() {
    return $("#tce_title_input").length > 0;
}

function isTestCaseViewFormLoaded() {
    return $("#tce_suite_id").length > 0;
}


/****************************************************************************
 * STEPS
 * Add step - append step html to steps container
 * remove step - update step indexes after that
 * swap steps
 ****************************************************************************/

$.fn.swapWith = function(to) {
    return this.each(function() {
        var copy_to = $(to).clone(true);
        var copy_from = $(this).clone(true);
        $(to).replaceWith(copy_from);
        $(this).replaceWith(copy_to);
    });
};

function addStep() {
    let stepNumber = $('.step').length + 1;
    renderStep(stepNumber)
    renderEditors();
}

function removeStep(btn) {
    $(btn).parent().parent().remove();
    updateStepsNumbers();
}

function stepUp(btn) {
    let step = $(btn).parent().parent();
    let previousStep = step.prev();
    step.swapWith(previousStep);
    updateStepsNumbers();
}

function stepDown(btn) {
    let step = $(btn).parent().parent();
    let nextStep = step.next();
    step.swapWith(nextStep);
    updateStepsNumbers();
}

function updateStepsNumbers() {
    $($(".step_number")).each(function (index) {
        let text = index + 1;
        $(this).text(text)
    });
}

/*
 * STEP HTML - is used in test case viewer create and update forms
 */

function renderStep(stepNumber) {
    let stepHtml = `
    <div class="row m-0 mt-2 p-0 step">
        <div class="col-auto p-0 d-flex flex-column align-items-center">
            <span class="fs-5 step_number">${stepNumber}</span>

            <button type="button" class="btn btn-outline btn-sm step_delete_btn px-1 py-0" onclick="stepUp(this)">
                <i class="bi bi-arrow-up-circle"></i>
            </button>

            <button type="button" class="btn btn-outline-danger btn-sm step_delete_btn px-1 py-0" onclick="removeStep(this)">
                <i class="bi bi-x-circle"></i>
            </button>

            <button type="button" class="btn btn-outline btn-sm step_delete_btn px-1 py-0" onclick="stepDown(this)">
                <i class="bi bi-arrow-down-circle"></i>
            </button>
        </div>

        <div class="col p-0 px-1 test_case_step">
            <textarea class="editor_textarea form-control border-secondary step_action" rows="2"></textarea>
        </div>
        <div class="col p-0 test_case_step">
            <textarea class="editor_textarea form-control border-secondary step_result" rows="2"></textarea>
        </div>
    </div>`;

    $("#steps_container").append(stepHtml)
}
