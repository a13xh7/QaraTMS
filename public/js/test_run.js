let currentCase;
testCaseAreaLocator = '#test_case_col'
chartAreaLocator = '#chart'

function loadTestCase(test_run_id, test_case_id) {
    $(testCaseAreaLocator).load(`/trc/${test_run_id}/${test_case_id}`, function () {

    });
}

function loadChart(test_run_id) {
    $(chartAreaLocator).load(`/trchart/${test_run_id}`, function () {

    });
}

/*
   const PASSED = 1;
    const FAILED = 2;
    const BLOCKED = 3;
    const NOT_TESTED = 4;
 */
function updateCaseStatus(test_run_id, test_case_id, status) {

    $.ajax({
        type: "POST",
        url: "/trcs",
        data: {
            'test_run_id': test_run_id,
            'test_case_id': test_case_id,
            'status': status
        },

        success: function (data) {  // response is case html and json

            if (status == 1) {
                $(`.result_badge[data-test_case_id='${test_case_id}']`).html('<span class="badge bg-success">Passed</span>');
            } else if (status == 2) {
                $(`.result_badge[data-test_case_id='${test_case_id}']`).html('<span class="badge bg-danger">Failed</span>');
            } else if (status == 3) {
                $(`.result_badge[data-test_case_id='${test_case_id}']`).html('<span class="badge bg-warning">Blocked</span>');
            } else if (status == 4) {
                $(`.result_badge[data-test_case_id='${test_case_id}']`).html('<span class="badge bg-secondary">Not Tested</span>');
            }

            loadChart(test_run_id); // reload chart

            $(".badge.bg-secondary").first().click(); // select next untested case
        }
    });
}

/*
   const PASSED = 1;
    const FAILED = 2;
    const BLOCKED = 3;
    const NOT_TESTED = 4;
 */

$('body').on('click', '.test_run_case_btn', function () {

    let status = $(this).attr('data-status');
    let test_run_id = $(this).attr('data-test_run_id');

    console.log(status)

    $('.test_run_case_btn').each(function () {

        let status = $(this).attr('data-status');

        if (status == 1) {
            $(this).removeClass("btn-success");
            $(this).addClass("btn-outline-success");
        } else if (status == 2) {
            $(this).removeClass("btn-danger");
            $(this).addClass("btn-outline-danger");
        } else if (status == 3) {
            $(this).removeClass("btn-warning");
            $(this).addClass("btn-outline-warning");
        } else if (status == 4) {
            $(this).removeClass("btn-secondary");
            $(this).addClass("btn-outline-secondary");
        }

    });

    if (status == 1) {
        $(this).removeClass("btn-outline-success");
        $(this).addClass("btn-success");
    } else if (status == 2) {
        $(this).removeClass("btn-outline-danger");
        $(this).addClass("btn-danger");
    } else if (status == 3) {
        $(this).removeClass("btn-outline-warning");
        $(this).addClass("btn-warning");
    } else if (status == 4) {
        $(this).removeClass("btn-outline-secondary");
        $(this).addClass("btn-secondary");
    }

});


$('body').on('click', '.tree_test_case', function () {

    $('.tree_test_case.selected_case').removeClass("selected_case");

    $(this).addClass('selected_case');
})
