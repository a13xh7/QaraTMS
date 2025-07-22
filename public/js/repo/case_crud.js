$("#test_cases_list").sortable({
    update: function (e, u) {
        updateCasesOrder();
    }
});

function updateCasesOrder() {
    var order = [];
    $('#test_cases_list .test_case').each(function (index, element) {
        order.push({
            id: $(this).attr('data-case_id'),
            order: index + 1
        });
    });
    console.log(order);

    $.ajax({
        url: "/tcuo",
        type: 'post',
        data: {
            order: order
        },
        success: function (result) {
        }
    });
}

function validateTestCaseFields() {
    // Clear previous error messages
    document.getElementById('epic_link_error').style.display = 'none';
    document.getElementById('platform_error').style.display = 'none';
    document.getElementById('empty_label_error').style.display = 'none';
    document.getElementById('title_error').style.display = 'none';
    document.getElementById('pre_conditions_error').style.display = 'none';
    document.getElementById('bdd_scenarios_error').style.display = 'none';

    // Get values
    const epic_link = document.getElementById('tce_epic_link').value;
    const platforms = document.querySelectorAll('input[name="platform"]:checked');
    const defaultLabel = document.getElementById('default_label');
    const title = document.getElementById('tce_title_input').value;
    const pre_conditions = document.getElementById('tce_preconditions_input').value;
    const bdd_scenarios = document.getElementById('tce_bdd_scenarios_input').value;

    let hasError = false;

    // Validate epic link
    if (!epic_link) {
        document.getElementById('epic_link_error').style.display = 'block';
        hasError = true;
    }

    // Validate platform selection
    if (platforms.length === 0) {
        document.getElementById('platform_error').style.display = 'block';
        hasError = true;
    }

    // Check if the default label "None" is still present
    if (defaultLabel) {
        document.getElementById('empty_label_error').style.display = 'block';
        hasError = true;
    }

    // Validate platform selection
    if (title.length === 0) {
        document.getElementById('title_error').style.display = 'block';
        hasError = true;
    }
    
    // Validate pre-condition
    if (pre_conditions.length === 0 || pre_conditions.trim() === "<br>") {
        document.getElementById('pre_conditions_error').style.display = 'block';
        hasError = true;
    }

    // Validate bdd scenario
    const bddKeywords = ["Given", "When", "Then"];
    const bddScenarioLower = bdd_scenarios.toLowerCase();
    const containsAllKeywords = bddKeywords.every(keyword => bddScenarioLower.includes(keyword.toLowerCase()));

    if (!bdd_scenarios && !containsAllKeywords) {
        document.getElementById('bdd_scenarios_error').style.display = 'block';
        hasError = true;
    }

    return hasError;
}

function getLabelsString() {
    const labelContainer = document.getElementById('tce_labels');
    const labels = Array.from(labelContainer.children).map(label => label.textContent.trim());
    return labels.join(';');
}

/****************************************************************************
 * Get data from CREATE and UPDATE test case form. Save data in object
 ****************************************************************************/

function getTestCaseDataFromForm() {
    let testCase = {};
    let platform = {};

    testCase.id = $("#tce_case_id").val();
    testCase.title = $("#tce_title_input").val();
    testCase.description = $("#tce_desc_input").val();
    testCase.labels = getLabelsString();
    testCase.suite_id = $("#tce_test_suite_select").val();
    testCase.automated = $("#tce_automated_select").val();
    testCase.priority = $("#tce_priority_select").val();
    testCase.regression = $("#tce_regression").val();
    testCase.epic_link = $("#tce_epic_link").val();
    testCase.linked_issue = $("#tce_linked_issue").val();
    platform.android = $("#tce_platform_android").is(':checked');
    platform.ios = $("#tce_platform_ios").is(':checked');
    platform.web = $("#tce_platform_web").is(':checked');
    platform.mweb = $("#tce_platform_mweb").is(':checked');
    testCase.platform = platform;

    testCase.release_version = $("#tce_release_version").val();
    testCase.severity = $("#tce_severity").val();

    testCase.data = {};
    if ($("#precond_from_cases").is(':checked')) {
        // Get the HTML content of selected test cases
        const selectedTestCasesHtml = document.getElementById('selected_test_cases').innerHTML;
        testCase.data['preconditions'] = selectedTestCasesHtml;
        testCase.data['precond_type'] = 'from_cases';
    } else {
        testCase.data['preconditions'] = $("#tce_preconditions_input").val();
        testCase.data['precond_type'] = 'free_text';
    }

    testCase.data['scenarios'] = $("#tce_bdd_scenarios_input").val();
    // testCase.data.steps = [];

    // $($(".step")).each(function (index) {

    //     if ($(this).find(".step_action").val() || $(this).find(".step_result").val()) {
    //         testCase.data.steps[index] =
    //             {
    //                 action: $(this).find(".step_action").val(),
    //                 result: $(this).find(".step_result").val()
    //             };
    //     }
    // });
    // console.log(testCase);
    return testCase;
}

/****************************************************************************
 * CREATE TEST CASE - server returns:
 *      test case tree html element
 *      json of created test case
 ****************************************************************************/

function createTestCase(addAnother = false) {
    if (validateTestCaseFields()) return;

    let newTestCase = getTestCaseDataFromForm();

    $.ajax({
        type: "POST",
        url: "/test-case/create",
        data: {
            'title': newTestCase.title,
            'description': newTestCase.description,
            'labels': newTestCase.labels,
            'suite_id': newTestCase.suite_id,
            'automated': newTestCase.automated,
            'priority': newTestCase.priority,
            'order': $('.test_case').length + 1,
            'data': JSON.stringify(newTestCase.data),
            'regression': newTestCase.regression,
            'epic_link': newTestCase.epic_link,
            'linked_issue': newTestCase.linked_issue,
            'platform': JSON.stringify(newTestCase.platform),
            'release_version': newTestCase.release_version,
            'severity': newTestCase.severity
        },

        success: function (data) {  // response is case html and json
            let testCase = $.parseJSON(data.json);

            if (addAnother) {
                loadTestCaseCreateForm();
            } else {
                renderTestCase(testCase.id)
            }

            loadCasesList(testCase.suite_id);
        }
    });
}

/****************************************************************************
 * UPDATE TEST CASE
 ****************************************************************************/

function updateTestCase() {
    if (validateTestCaseFields()) return;

    let updatingTestCase = getTestCaseDataFromForm();

    $.ajax({
        type: "POST",
        url: "/test-case/update",
        data: {
            'id': updatingTestCase.id,
            'title': updatingTestCase.title,
            'description': updatingTestCase.description,
            'labels': updatingTestCase.labels,
            'suite_id': updatingTestCase.suite_id,
            'automated': updatingTestCase.automated,
            'priority': updatingTestCase.priority,
            'data': JSON.stringify(updatingTestCase.data),
            'regression': updatingTestCase.regression,
            'epic_link': updatingTestCase.epic_link,
            'linked_issue': updatingTestCase.linked_issue,
            'platform': JSON.stringify(updatingTestCase.platform),
            'release_version': updatingTestCase.release_version,
            'severity': updatingTestCase.severity
        },
        success: function (data) {  // response is case html and json
            let testCase = $.parseJSON(data.json);
            renderTestCase(testCase.id)
            loadCasesList(testCase.suite_id);
        }
    });
}

/****************************************************************************
 * DELETE TEST CASE - delete from list
 ****************************************************************************/
function deleteTestCase(id) {
    if (!confirm("Are you sure you want to delete this test case?")) {
        return;
    }
    
    const $caseElement = $("[data-case_id=" + id + "]");
    $caseElement.addClass("deleting");
    
    $.ajax({
        url: "/test-case/delete",
        method: "POST",
        data: {
            "id": id,
        },
        success: function (data) {
            $caseElement.remove();

            if ($('#tce_case_id').val() === id || $('#tce_case_id').text() === id) {
                closeTestCaseEditor();
            }
        },
        error: function(xhr, status, error) {
            $caseElement.removeClass("deleting");
            
            alert("Failed to delete test case: " + error);
            console.error("Delete test case error:", error);
        }
    });
}

