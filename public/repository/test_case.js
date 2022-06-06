/****************************************************************************
 * TEST CASE AREA in repository. Load TEST CASE forms
 * close viewer
 * close viewer by pressing Esc but only for read_block

 ****************************************************************************/

let testCaseAreaLocator = "#test_case_area";

function loadTestCaseCreateForm() {

    let url = suite_id ? `/tc/create/${repository_id}` : `/tc/create/${repository_id}/${suite_id}`;

    $(testCaseAreaLocator).load(url, function() {
        $('textarea').autoResize();
    });
}

function renderTestCase(test_case_id) {
    $(testCaseAreaLocator).load(`/tc/${test_case_id}`, function() {

        $(`.test_case`).removeClass("selected");
        $(`.test_case[data-case_id='${test_case_id}']`).addClass('selected');
    });
}















let oldParentId='';
function renderTestCaseEditForm(test_case_id) {
    $(testCaseAreaLocator).load(`/tc/${test_case_id}/edit`, function() {
        $('textarea').autoResize();
        oldParentId = $("#tccf_test_suite_select").val();
    });
}

function closeTestCaseViewer() {
    $('#test_case_block').remove();
}

$(document).on('keydown', function(event) {
    if (event.key == "Escape") {
        if($('#tccf_title_input').length <= 0) {
            closeTestCaseViewer();
        }
    }
});


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

/****************************************************************************
 * Get data from CREATE and UPDATE test case form. Save data in object
 ****************************************************************************/

function getTestCaseDataFromForm() {
    let testCase = {};

    testCase.id = $("#tccf_case_id").val()
    testCase.title = $("#tccf_title_input").val();
    testCase.suite_id = $("#tccf_test_suite_select").val();
    testCase.automated = $("#tccf_automated_select").val();

    testCase.data = {};
    testCase.data['preconditions'] = $("#tccf_preconditions_input").val();
    testCase.data.steps = [];

    $( $(".step") ).each( function(index) {

        if($(this).find(".step_action").val() || $(this).find(".step_result").val())
        {
            testCase.data.steps[index] =
                {
                    action: $(this).find(".step_action").val(),
                    result: $(this).find(".step_result").val()
                };
        }
    });
    return testCase;
}

/****************************************************************************
 * CREATE TEST CASE - server returns:
 *      test case tree html element
 *      json of created test case
 *
 * Append case html to the tree
 * Show new test case in viewer
 ****************************************************************************/

function createTestCase() {
    let newTestCase = getTestCaseDataFromForm();

    if(!newTestCase.title) {
        alert('Title is required');
        return;
    }

    $.ajax({
        type: "POST",
        url: "/test-case/create",
        data: {
            'title': newTestCase.title,
            'suite_id': newTestCase.suite_id,
            'automated': newTestCase.automated,
            'data': JSON.stringify(newTestCase.data)
        },

        success: function (data) {  // response is case html and json
            let testCase = $.parseJSON(data.json);
            loadCasesList(testCase.suite_id);
        }
    });
}

/****************************************************************************
 * UPDATE TEST CASE - server returns:
 *      test case tree html element
 *      json of created test case
 *
 * Find case html to the tree and change title
 * Show new test case in viewer
 * TODO remove case from tree and add on case parent test suite has changed
 ****************************************************************************/

function updateTestCase() {
    let updatingTestCase = getTestCaseDataFromForm();

    if(!updatingTestCase.title) {
        alert('Title is required');
        return;
    }

    $.ajax({
        type: "POST",
        url: "/test-case/update",
        data: {
            'id': updatingTestCase.id,
            'title': updatingTestCase.title,
            'suite_id': updatingTestCase.suite_id,
            'automated': updatingTestCase.automated,
            'data': JSON.stringify(updatingTestCase.data)
        },

        success: function (data) {  // response is case html and json
            let testCase = $.parseJSON(data.json);

            if(oldParentId != testCase.suite_id) {
                $(`[data-test_case_id='${testCase.id}']`).remove();
                $(`[data-suite_id='${testCase.suite_id}'] > .tree_suite_test_cases`).append(data.html);
            } else {
                $(`[data-test_case_id='${testCase.id}']`).replaceWith(data.html);
            }

            renderTestCase(testCase.id)
        }
    });
}


/****************************************************************************
 * DELETE TEST CASE
 * delete case from tree
 ****************************************************************************/

function deleteTestCase(id) {
    $.ajax({
        url: "/test-case/delete",
        method: "POST",
        data: {
            "id": id,
        },
        success: function (data) {
            $("[data-test_case_id=" + id +"]").remove();
        }
    });
}


