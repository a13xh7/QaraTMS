// Tree variables
let tree = $("#tree");
let treeData = [];
const sortable = new TreeSortable();

let suiteFormTitleInput = $('#test_suite_title_input');
/**************************************************
 * LOAD TREE
 *************************************************/

function loadSuitesTree() {
    $.ajax({
        url: `/repo/${repository_id}`,
        method: "GET",

        success: function (data) {
            treeData = data;
            const content = treeData.map(sortable.createBranch);
            tree.html(content);
            sortable.run();

        }, complete: function (data) {
            selectLastActiveSuite();
        }
    });
}

function selectLastActiveSuite() {

    if($('#tree li').length > 0) {

        if(Cookies.get('lastSelectedSuite') != null) {
            $(`#tree li[id="${Cookies.get('lastSelectedSuite')}"] .left-sidebar`).click();
        } else {
            $('#tree li .left-sidebar').first().click();
        }

    } else {
        $('#test_cases_list').empty();
        closeTestCaseEditor();
    }
}
/**************************************************
 * SUITE FORM - create / edit
 *************************************************/

function showSuiteForm(operation, suite_id = null) {

    activeTreeSuiteItem.setId(suite_id);

    if(operation == 'create') {
        $('#tsf_title').text('Create Test Suite');
        $('#tsf_create_btn').show();
        $('#tsf_update_btn').hide();
        suiteFormTitleInput.val('');

    } else if(operation == 'edit') {
        $('#tsf_title').text('Edit Test Suite');
        $('#tsf_update_btn').show();
        $('#tsf_create_btn').hide();
        suiteFormTitleInput.val(activeTreeSuiteItem.getTitle());
    }

    $('#test_suite_form_overlay').show();
}

function closeSuiteForm() {
    $('#test_suite_form_overlay').hide();
}


/**************************************************
 * SORT - update parent and order
 *************************************************/

sortable.onSortCompleted(async (event, ui) => {
    let suite_id = ui.item.attr('data-mid');
    let suite_parent_id = ui.item.getParent().attr('data-mid');

    updateSuiteParent(suite_id, suite_parent_id);
    updateSuitesOrder();
    // loadCasesList(suite_id);
});

function updateSuitesOrder() {
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
}

/**************************************************
 * SUITE CREATE
 *************************************************/
function createSuite() {
    if(!suiteFormTitleInput.val()) {
        alert('Title is required');
        return;
    }

    $.ajax({
        type: "POST",
        url: "/test-suite/create",
        data: {
            'repository_id': $('#repository_id').val(),
            'parent_id': activeTreeSuiteItem.getId(),
            'title': suiteFormTitleInput.val(),
        },
        success: function (data) {
            let newSuite = $.parseJSON(data.json);

            if(newSuite.parent_id) {
                activeTreeSuiteItem.addChild(newSuite.id, newSuite.parent_id);
            } else {
                activeTreeSuiteItem.addRootChild(newSuite.id, newSuite.parent_id)
            }
            closeSuiteForm();
            updateSuitesOrder();
        }
    });
}

// form - handle press enter
$("#test_suite_form_overlay form").submit(function() {
    return false;
});

$("#test_suite_title_input").keyup(function(event) {
    if (event.keyCode === 13 && $("#tsf_create_btn").is(":visible")) {
        $("#tsf_create_btn").click();
    } else if(event.keyCode === 13 && $("#tsf_update_btn").is(":visible")) {
        $("#tsf_update_btn").click();
    }
});


/**************************************************
 * SUITE UPDATE
 *************************************************/
function updateSuite() {
    if(!suiteFormTitleInput.val()) {
        alert('Title is required');
        return;
    }

    $.ajax({
        type: "POST",
        url: "/test-suite/update",
        data: {
            'id': activeTreeSuiteItem.getId(),
            'title': suiteFormTitleInput.val(),
        },

        success: function (data) {
            $(`#suite_title_${activeTreeSuiteItem.getId()}`).text(suiteFormTitleInput.val());
            $('#test_cases_list_site_title').text(suiteFormTitleInput.val());
            closeSuiteForm();
        }
    });
}

/**************************************************
 * SUITE - Update parent
 *************************************************/
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

/**************************************************
 * SUITE DELETE
 *************************************************/
function deleteSuite(id) {
    const confirm = window.confirm("Are you sure you want to delete this test suite? All child test suited and test cases will be deleted. ");
    if (!confirm) {
        return;
    }

    activeTreeSuiteItem.setId(id)
    let was_selected = activeTreeSuiteItem.isElementSelected(id);

    $.ajax({
        url: "/test-suite/delete",
        method: "POST",
        data: {
            "id": id,
        },
        success: function (data) {
            activeTreeSuiteItem.removeSelfFromTree();

            if(isTestCaseCreateOrEditFormLoaded()) {
                $(`#tce_test_suite_select option[value='${id}']`).remove();
            }

            if(isTestCaseViewFormLoaded() && $('#tce_suite_id').val() == id) {
                console.log('close eddddd')
                closeTestCaseEditor();
            }

            if(was_selected) {
                selectLastActiveSuite();
                console.log('choose first')
            }

        }
    });
}


/**************************************************
 * SUITE object. set id before working with it
 *************************************************/
let activeTreeSuiteItem = {

    id: null,

    setId(id) {
        this.id = id;
    },

    getId() {
        return this.id;
    },

    getParentId() {
        return $(`#tree li[data-mid='${this.id}']`).attr('data-pid');
    },

    getTitle() {
        return $(`#suite_title_${this.id}`).text();
    },

    getElement() {
        return $(`#tree li[data-mid=${this.id}]`);
    },

    findElement(id) {
        return $(`#tree li[data-mid=${id}]`);
    },

    isElementSelected(id) {
        return $(`#tree li[data-mid=${id}] .branch-wrapper`).hasClass("selected");
    },

    addChild(id, parent_id) {
        this.getElement().addChildBranch(id, parent_id);
    },

    addRootChild(id, parent_id) {
        if( $('#tree li').length > 0) {
            $('#tree li').first().addSiblingBranch(id, parent_id);
        } else {
            location.reload();
        }
    },

    removeSelfFromTree() {
        this.getElement().removeBranch();
    },

    addSelectedClass() {
        this.getElement().find('.branch-wrapper').addClass('selected');
    }
};
