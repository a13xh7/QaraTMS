// Tree variables
let tree = $("#tree");
let treeData = [];
const sortable = new TreeSortable();

// Suite variables
let suite_id = null
let parent_id = null
let treeItem = null;
let title = null;
suiteTitleInput = $('#test_suite_title_input');

function showCreateSuiteForm() {
    suiteTitleInput.val('');
    $('#test_suite_overlay').show();
}

function closeSuiteForm() {
    $('#test_suite_overlay').hide();
}

function createSuite() {
    if(!suiteTitleInput.val()) {
        alert('Title is required');
        return;
    }

    $.ajax({
        type: "POST",
        url: "/test-suite/create",
        data: {
            'repository_id': $('#repository_id').val(),
            'parent_id': parent_id,
            'title': suiteTitleInput.val(),
        },
        success: function (data) {
            let newSuite = $.parseJSON(data.json);

            if(parent_id) {
                treeItem.addChildBranch(newSuite.id, newSuite.parent_id);
            } else {
                if( $('#tree li').length > 0) {
                    $('#tree li').first().addSiblingBranch(newSuite.id, newSuite.parent_id);
                } else {
                    location.reload();
                }
            }

            closeSuiteForm();
        }
    });
}

function updateSuiteParent(id, parent_id) {
    $.ajax({
        type: "POST",
        url: "/tsup",
        data: {
            'id': id,
            'parent_id': parent_id,
        },
        success: function (data) {

        }
    });
}

function deleteSuite(id) {
    $.ajax({
        url: "/test-suite/delete",
        method: "POST",
        data: {
            "id": id,
        },
        success: function (data) {
            treeItem.removeBranch();
        }
    });
}

function loadTree() {
    $.ajax({
        url: "/repo/1",
        method: "GET",

        success: function (data) {
            treeData = data;
            // console.log(treeData)
            const content = treeData.map(sortable.createBranch);
            tree.html(content);
            sortable.run();
        }
    });
}

function loadCasesList(id, element) {
    suite_id = id;
    title = $(`#suite_title_${id}`).text();

    $('#tree .branch-wrapper').removeClass("selected")
    $($(element).parent().addClass('selected'));

    $('#test_cases_list_site_title').text(title);

    $('#test_cases_list').load(`/tscl/${suite_id}`, function() { });
}

$('body').on("click", "#toogle_collaple_expand", function (e) {
    let suite_id = $(this).parent().parent().parent().parent().data('mid');
    //
    // $(`li[data-pid="${suite_id}"]`).each(function (el) {
    //    console.log(22);
    // });
});

function rec(element) {
    let suite_id = element.parent().parent().parent().parent().data('mid');

    if($(`li[data-pid="${suite_id}"]`).length > 0) {
        rec(element)
    } else {
        element.toggle();
    }

}


$(document).ready(function () {

    loadTree();

    /**
     * ADD ROOT SUITE
     */
    $(document).on("click", "#add_root_suite_btn", function (e) {
        e.preventDefault();
        parent_id = null;
        treeItem = $(this);
        showCreateSuiteForm();
    });

    /**
     * ADD CHILD SUITE
     */
    $(document).on("click", "#add_child_suite_btn", function (e) {
        e.preventDefault();
        parent_id = $(this).parent().parent().parent().parent().data('mid');
        treeItem = $(this);
        showCreateSuiteForm();
    });


    sortable.onSortCompleted(async (event, ui) => {
        suite_id = ui.item.attr('data-mid');
        parent_id = ui.item.getParent().attr('data-mid');

        updateSuiteParent(suite_id, parent_id);

        var order = [];
        $('#tree li').each(function(index,element) {
            order.push({
                id: $(this).attr('data-mid'),
                order: index+1
            });
        });

        $.ajax({
            url: "/tsuo",
            type: 'post',
            data: {
                order: order
            },
            success: function (result) {
            }
        });

    });


    $(document).on("click", ".remove-branch", function (e) {
        e.preventDefault();

        const confirm = window.confirm("Are you sure you want to delete this test suite? All child test suited and test cases will be deleted. ");
        if (!confirm) {
            return;
        }

        treeItem = $(this);
        suite_id = treeItem.parent().parent().parent().parent().data('mid');

        deleteSuite(suite_id);
    });
});



