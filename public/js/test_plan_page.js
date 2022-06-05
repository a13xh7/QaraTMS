
function renderPlanTree(select) {
    $("#tree").load(`/tpt/${select.value}`, function() {

    });
}

function selectAllTestPlanCases() {
    $(".test_suite_cbx").each(function(index) {
        $(this).prop('checked',false);
        $(this).click();
    });
}

function deselectAllTestPlanCases() {
    $(".test_suite_cbx").each(function(index) {
        $(this).prop('checked',false);
    });

    $(".test_case_cbx").each(function(index) {
        $(this).prop('checked',false);
    });

    $('#test_plan_data').val('');
}


/****************************************************************************
CHECKBOXES
 ****************************************************************************/

$( document ).ready(function() {


    // Чекбокс test suite
    $("body").on( "change", ".test_suite_cbx", function() {

        var test_suite_id = $(this).attr('data-test_suite_id');

        if(this.checked) {
            $(`.test_case_cbx[data-test_suite_id=${test_suite_id}]`).prop('checked', true);  // отметить все текст кейсы сьюта
            $(`.test_suite_cbx[data-parent_id=${test_suite_id}]`).click(); //
        } else {
             $(`.test_case_cbx[data-test_suite_id=${test_suite_id}]`).prop('checked', false);
             $(`.test_suite_cbx[data-parent_id=${test_suite_id}]`).click();
        }

        updateFormDataField();
    });

    $("body").on( "change", ".test_case_cbx", function() {

        var test_suite_id = $(this).attr('data-test_suite_id');

        var test_suite_selector = `.test_suite_cbx[data-test_suite_id=${test_suite_id}]`;
        var test_case_selector = `.test_case_cbx[data-test_suite_id=${test_suite_id}]`;

        // найти все тест кейсы  с указанным test suite id
        var status = [];
        $(test_case_selector).each(function(index) {
            status.push($(this).prop('checked'))
        });

        if(status.includes(false)) {
            $(test_suite_selector).prop('checked', false);
        } else {
            $(test_suite_selector).prop('checked', true);
        }

        updateFormDataField();
    });


    function updateFormDataField() {
        var selected = [];

        $('input.test_case_cbx:checked').each(function() {
            selected.push($(this).attr('data-test_case_id'));
        });

        $('#test_plan_data').val(selected);
    }

});
