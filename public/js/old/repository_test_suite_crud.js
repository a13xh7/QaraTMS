/****************************************************************************
 TEST SUITE CREATE FORM - show / hide
 ****************************************************************************/

function renderTestSuiteEditor(operation, repository_id, test_suite_id) {

    let url = `/tse/${operation}/${repository_id}`;

    if(test_suite_id) {
        url+= `/${test_suite_id}`;
    }

    $("#tse_area").load(url, function() {
        $("#test_suite_editor").modal('show');
    });
}

function closeTestSuiteEditor() {
    $('#test_suite_editor').remove();
}

/****************************************************************************
 * Attach test suite html to the tree
 ****************************************************************************/
// ajax response data
function addToTheTree(data) {
    let testSuite = $.parseJSON(data.json);
    let testSuiteLocator = `.tree_suite[data-suite_id='${testSuite.id}']`;
    let parentSuiteLocator = `.tree_suite[data-suite_id='${testSuite.parent_id}']`;

    // Remove test suite from the tree
    // If changing parent suite we must delete suite from the tree and attach it to the new parent
    if($(testSuiteLocator).length > 0) {
        $(testSuiteLocator).remove();
    }

    if($(parentSuiteLocator).length > 0) {
        $(parentSuiteLocator).append(data.html); // attach suite to parent suite
    } else {
        $(`#tree`).append(data.html); // if there is no parent suite. suite is repo child
    }
}

/****************************************************************************
 * Get data from CREATE /  UPDATE test suite form. Save data in object
 ****************************************************************************/

function getTestSuiteDataFromForm() {
    let testSuite = {};

    testSuite.id = $("#tse_id").val();
    testSuite.repository_id = $("#tse_repository_id").val();
    testSuite.parent_id = $("#tse_parent_id").val();
    testSuite.title = $("#tse_title").val();
    return testSuite;
}

/****************************************************************************
 * CREATE TEST SUITE - server returns:
 *      test suite tree html element
 *
 * Append suite html to the tree
 ****************************************************************************/

function createTestSuite() {
    let newTestSuite = getTestSuiteDataFromForm();

    if(!newTestSuite.title) {
        alert('Title is required');
        return;
    }

    $.ajax({
        type: "POST",
        url: "/test-suite/create",
        data: {
            'repository_id': newTestSuite.repository_id,
            'parent_id': newTestSuite.parent_id,
            'title': newTestSuite.title,
        },

        success: function (data) {  // response is test suite html for the tree and suite json
            closeTestSuiteEditor();
            addToTheTree(data)
        }
    });
}

/****************************************************************************
 * UPDATE TEST SUITE - server returns:
 *      suite json
 *
 * Append suite html to the tree
 ****************************************************************************/

// function updateTestSuite() {
//     let testSuite = getTestSuiteDataFromForm();
//
//     if(!testSuite.title) {
//         alert('Title is required');
//         return;
//     }
//
//     $.ajax({
//         type: "POST",
//         url: "/test-suite/update",
//         data: {
//             'id': testSuite.id,
//             'parent_id': testSuite.parent_id,
//             'title': testSuite.title,
//         },
//
//         success: function (data) {  // responce is tree test suite html
//             location.reload();
//             // closeTestSuiteEditor();
//             // addToTheTree(data)
//         }
//     });
// }


/****************************************************************************
 * DELETE TEST SUITE
 * delete test suite from tree - all children also will be removed
 ****************************************************************************/

function deleteTestSuite(id) {
    $.ajax({
        url: "/test-suite/delete",
        method: "POST",
        data: {
            "id": id,
        },
        success: function (data) {
            $(`.tree_suite[data-suite_id='${id}']`).remove();
        },
        error: function(xhr, status, error){
            var errorMessage = xhr.status + ': ' + xhr.statusText
            alert('Error - ' + errorMessage);
        }
    });
}

