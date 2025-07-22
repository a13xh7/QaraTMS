let savedSelectedTestCases = [];

function renderPlanTree(select) {
    $("#tree").load(`/tpt/${select.value}`, function () {
        savedSelectedTestCases.forEach(function (testCaseId) {
            $(`.test_case_cbx[data-test_case_id=${testCaseId}]`).prop('checked', true);
        });
        // Update parent suites after restoring selections
        $('.test_case_cbx:checked').each(function() {
            updateParentSuites($(this));
        });
    });
}

function restoreSelectedTestCases() {
    const testCaseIds = $("#test_plan_data").val().split(',');
    if ($("#test_plan_data").val() !== "") {
        testCaseIds.forEach(function (testCaseId) {
            savedSelectedTestCases.push(testCaseId);
            $(`.test_case_cbx[data-test_case_id=${testCaseId}]`).prop('checked', true);
        });
        
        // Update parent suites after restoring selections
        $('.test_case_cbx:checked').each(function() {
            updateParentSuites($(this));
        });
    }
}

function selectAllTestCasesBySuiteId(suiteId) {
    let tmpSavedSelectedTestCases = [];
    $(`.test_case_cbx[data-test_suite_id=${suiteId}]`).each(function (index) {
        $(this).prop('checked', true);
        const testCaseId = $(this).attr('data-test_case_id');
        if (testCaseId !== undefined) {
            tmpSavedSelectedTestCases.push(testCaseId);
        }
    });

    $(`.test_suite_cbx[data-test_suite_id=${suiteId}]`).prop('checked', true);

    savedSelectedTestCases = savedSelectedTestCases.concat(tmpSavedSelectedTestCases);
    $('#test_plan_data').val(savedSelectedTestCases);
}

function deselectAllTestCasesBySuiteId(suiteId) {
    $(`.test_case_cbx[data-test_suite_id=${suiteId}]`).each(function (index) {
        $(this).prop('checked', false);
        const testCaseId = $(this).attr('data-test_case_id');
        if (testCaseId !== undefined) {
            savedSelectedTestCases.splice(savedSelectedTestCases.indexOf(testCaseId), 1);
        }
    });

    $(`.test_suite_cbx[data-test_suite_id=${suiteId}]`).prop('checked', false);
    $('#test_plan_data').val(savedSelectedTestCases);
}

function selectAllTestPlanCases() {
    let tmpSavedSelectedTestCases = [];
    $(".test_suite_cbx, .test_case_cbx").each(function (index) {
        $(this).prop('checked', true)
        const testCaseId = $(this).attr('data-test_case_id');
        if (testCaseId !== undefined) {
            tmpSavedSelectedTestCases.push(testCaseId);
        }
    });
    savedSelectedTestCases = tmpSavedSelectedTestCases;
    $('#test_plan_data').val(savedSelectedTestCases);
}

function deselectAllTestPlanCases() {
    $(".test_suite_cbx, .test_case_cbx").each(function () {
        $(this).prop('checked', false);
    });
    savedSelectedTestCases = [];
    $('#test_plan_data').val('');
}

function initializeCollapsible() {
    const collapsibleElements = document.getElementsByClassName("suiteTitle");
    
    Array.from(collapsibleElements).forEach(element => {
        element.removeAttribute('onclick');
        
        element.addEventListener("click", function() {
            this.classList.toggle("active");
            const content = this.closest('span').nextElementSibling;
            if (content.style.display === "block") {
                content.style.display = "none";
            } else {
                content.style.display = "block";
            }
        });
    });
}

function toggleTestSuiteById(suiteId) {
    $(`.suiteTitle[data-test_suite_id="${suiteId}"]`).each(function () {
        const content = $(this).closest('span').next();
        if (content.is(':visible')) {
            content.hide();
            $(this).removeClass("active");
        } else {
            content.show();
            $(this).addClass("active");
        }
    });
}


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
    $('#test_plan_data').val(savedSelectedTestCases);
}
/****************************************************************************
 CHECKBOXES
 ****************************************************************************/

$(document).ready(function () {
    $("body").on("change", ".test_suite_cbx, .test_case_cbx", function () {
        if ($(this).hasClass("test_suite_cbx")) {
            const suiteId = $(this).attr('data-test_suite_id');
            if ($(this).prop("checked")) {
                $(this).prop('checked', true)
                selectAllTestCasesBySuiteId(suiteId);
            } else {
                $(this).prop('checked', false)
                deselectAllTestCasesBySuiteId(suiteId);
            }
        }
        const testCaseId = $(this).attr('data-test_case_id');
        if ($(this).prop("checked")) {
            if (testCaseId !== undefined) {
                $(this).prop('checked', true)
                savedSelectedTestCases.push(testCaseId);
            }
        } else {
            if (testCaseId !== undefined) {
                $(this).prop('checked', false)
                const index = savedSelectedTestCases.indexOf(testCaseId);
                savedSelectedTestCases.splice(index, 1);
            }
        }

        updateParentSuites($(this));

        updateFormDataField();
    });

    restoreSelectedTestCases();
});
