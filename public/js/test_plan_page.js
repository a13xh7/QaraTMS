function renderPlanTree(select) {
    $("#tree").load(`/tpt/${select.value}`, function () {

    });
}

function selectAllTestPlanCases() {
    const selectedTestCases = [];
    $(".test_suite_cbx, .test_case_cbx").each(function (index) {
        $(this).prop('checked', true)
        const testCaseId = $(this).attr('data-test_case_id');
        // add test case ids to selectedTestCases
        if (testCaseId !== undefined) {
            selectedTestCases.push(testCaseId);
        }
    });
    $('#test_plan_data').val(selectedTestCases);
}

function deselectAllTestPlanCases() {
    $(".test_suite_cbx, .test_case_cbx").each(function () {
        $(this).prop('checked', false);
    });
    $('#test_plan_data').val('');
}

/****************************************************************************
 CHECKBOXES
 ****************************************************************************/

$(document).ready(function () {
    $("body").on("change", ".test_suite_cbx, .test_case_cbx", function () {
        if ($(this).hasClass("test_suite_cbx")) {
            // go up to the tree_suite element and update all children to the new value
            $(this)
                .closest(".tree_suite")
                .find(".test_suite_cbx, .test_case_cbx")
                .prop("checked", $(this).prop("checked"));
        }
        // update the parent suite(s) state
        updateParentSuites($(this));

        updateFormDataField();
    });

    /**
     * Recursive function that updates all parent suites of a test suite or test case.
     *
     * @param $element - jQuery Element of test suite or test case checkbox
     */
    function updateParentSuites($element) {
        // check, if we are on a test suite checkbox and get parent
        const parentTestSuiteId = $element.hasClass("test_suite_cbx") ?
            $element.attr("data-parent_id") :
            $element.attr("data-test_suite_id");
        if (parentTestSuiteId) {
            const $parentTestSuite = $(`.test_suite_cbx[data-test_suite_id=${parentTestSuiteId}]`)
            const childTestSuiteSelector = `.test_suite_cbx[data-parent_id=${parentTestSuiteId}]`;
            const childTestCaseSelector = `.test_case_cbx[data-test_suite_id=${parentTestSuiteId}]`;
            // check if all children are selected
            const allChildrenChecked = $parentTestSuite
                .closest(".tree_suite")
                .find([childTestSuiteSelector, childTestCaseSelector].join())
                .toArray()
                .every(function (childElement) {
                    // normal DOM element, not jQuery element
                    return childElement.checked;
                });
            $parentTestSuite.prop("checked", allChildrenChecked);
            // continue with parent
            updateParentSuites($parentTestSuite);
        }
    }

    function updateFormDataField() {
        const selected = [];
        $('input.test_case_cbx:checked').each(function () {
            selected.push($(this).attr('data-test_case_id'));
        });
        $('#test_plan_data').val(selected);
    }
});
