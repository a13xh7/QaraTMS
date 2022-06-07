/**************************************************
 * LOAD SCRIPTS
 *************************************************/

$.getScript('/js/repo/tree.js', function() {});
$.getScript('/js/repo/suites_tree_and_crud.js', function() {});
$.getScript('/js/repo/case_crud.js', function() {});
/**************************************************
 * RENDER SUITES TREE
 * and select first available suite
 * when all scripts are loaded
 *************************************************/
$.getScript('/js/repo/case_editor.js', function() {
    loadSuitesTree();
});



/**************************************************
 * Click on test suite - load suite test cases
 *************************************************/

function loadCasesList(id, element) {
    activeTreeSuiteItem.setId(id);

    // Add selected class
    $('#tree .branch-wrapper').removeClass("selected")
    activeTreeSuiteItem.addSelectedClass();

    $('#test_cases_list_site_title').text(activeTreeSuiteItem.getTitle()); // set title in test cases list area
    $('#test_cases_list').load(`/tscl/${activeTreeSuiteItem.getId()}`, function() { }); // load test cases
}

function expandSuitesList() {
    $('#test_cases_list_col').addClass('col-9').removeClass('col')
}

function collapseSuitesList() {
    $('#test_cases_list_col').addClass('col').removeClass('col-9')
}

// BLOCK ANY BUTTON AFTER CLICK to prevent ajax errors

$("body").on('click', 'button', function () {
    let button = $(this).prop('disabled', true);
    setTimeout(function() {
        button.prop('disabled', false);
    }, 500);
});


// $('body').on("click", "#toogle_collaple_expand", function (e) {
//     let suite_id = $(this).parent().parent().parent().parent().data('mid');
// });
//
// function rec(element) {
//     let suite_id = element.parent().parent().parent().parent().data('mid');
//
//     if($(`li[data-pid="${suite_id}"]`).length > 0) {
//         rec(element)
//     } else {
//         element.toggle();
//     }
//
// }




