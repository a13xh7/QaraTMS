/**************************************************
 * LOAD SCRIPTS
 *************************************************/

// $.getScript("/js/repo/tree.js", function () {
//     $.getScript("/js/repo/test_suite_tree_crud.js", function () {
//         $.getScript("/js/repo/test_case.js", function () {
//             /**************************************************
//              * RENDER SUITES TREE
//              * and select first available suite
//              * when all scripts are loaded
//              *************************************************/
//
//             $.getScript("/js/repo/test_case.js", function () {
//                 try {
//                     loadSuitesTree();
//                 } catch (e) {
//                     setTimeout(function () {
//                         loadSuitesTree();
//                     }, 1000);
//                 }
//             });
//         });
//     });
// });

(async function initRepoScripts() {
    try {
        await $.getScript("/js/repo/tree.js");
        await $.getScript("/js/repo/test_suite_tree_crud.js");
        await $.getScript("/js/repo/test_case.js");

        $(function () {
            loadSuitesTree();
        });

    } catch (error) {
        console.error("JS scripts loading error", error);
    }
})();

/**************************************************
 * Click on test suite - load suite test cases
 *************************************************/

function loadCasesList(id) {
    activeTreeSuiteItem.setId(id);

    Cookies.set('lastSelectedSuite', id);

    // Add selected class
    $('#tree .branch-wrapper').removeClass("selected");
    activeTreeSuiteItem.addSelectedClass();

    $('#test_cases_list_site_title').text(activeTreeSuiteItem.getTitle()); // set title in test cases list area
    $('#test_cases_list').load(`/tscl/${activeTreeSuiteItem.getId()}`, function () {
    }); // load test cases
}

/**************************************************
 * Collapse / expand test cases list
 **************************************************/
function expandCasesList() {
    $('#test_cases_list_col').addClass('col-9').removeClass('col')
}

function collapseCasesList() {
    $('#test_cases_list_col').addClass('col').removeClass('col-9')
}

/**************************************************
 *  BLOCK ANY BUTTON AFTER CLICK
 *  to prevent ajax errors, double input
 **************************************************/

$("body").on('click', 'button', function () {
    let button = $(this).prop('disabled', true);
    setTimeout(function () {
        button.prop('disabled', false);
    }, 250);
});

/**************************************************
 * Collapse / expand children
 **************************************************/

$('body').on("click", "#toogle_collaple_expand", function (e) {
    let suite_id = $(this).parent().parent().parent().parent().data('mid');

    rec(suite_id)
});

function rec(suite_id) {
    let child_li = $(`li[data-pid='${suite_id}']`);

    if (child_li.is(":visible")) {
        child_li.hide();
    } else {
        child_li.show();
    }

    if ($(`li[data-pid='${child_li.attr('data-mid')}']`).length > 0) {
        rec(child_li.attr('data-mid'))
    }
}
