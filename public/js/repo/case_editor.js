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
 ****************************************************************************/
function addStep() {
    let stepNumber = $('.step').length + 1;
    renderStep(stepNumber)
}

function removeStep(btn) {
    $(btn).parent().parent().remove();

    $($(".step_number")).each(function (index) {
        let text = index + 1;
        $(this).text(text)
        console.log(text);
    });
}

/*
 * STEP HTML - is used in test case viewer create and update forms
 */

function renderStep(stepNumber) {
    let stepHtml = `
         <div class="row m-0 p-0 step">

            <div class="col-auto p-0 pt-4">
                <span class="fs-5 step_number">${stepNumber}</span>
            </div>

            <div class="col">
                <label class="form-label m-0"><b>Action</b></label>
                <textarea class="form-control border-secondary step_action" rows="2"></textarea>
            </div>

            <div class="col">
                <label class="form-label m-0"><b>Expected Result</b></label>
                <textarea class="form-control border-secondary step_result" rows="2"></textarea>
            </div>

            <div class="col-auto p-0 pt-4">
                <button type="button" class="btn btn-outline-danger btn-sm step_delete_btn" onclick="removeStep(this)">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>

        </div>`;

    $("#steps_container").append(stepHtml)
}
