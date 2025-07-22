let currentCase;
testCaseAreaLocator = '#test_case_col'
chartAreaLocator = '#chart'

let attachmentModal;
let modalAttachmentContent;
let bsModalInstance = null;

$(document).ready(function () {
    $('.selected_assignee').on('change', function () {
        var userId = $(this).val();
        var testCaseId = $(this).data('testcase');
        var testRunId = $(this).data('testrun');
        updateAssignee(userId, testCaseId, testRunId);
    });

    $('body').on('click', '.attachment-trigger', function (event) {
        event.preventDefault();

        const targetModalSelector = $(this).data('bs-target');
        const targetModal = $(targetModalSelector);

        if (targetModal.length) {
            try {
                const bsModalInstance = bootstrap.Modal.getOrCreateInstance(targetModal[0]);
                bsModalInstance.show(this);
            } catch (e) {
                console.error(">>> Failed to get or create Bootstrap Modal instance upon click:", e);
                alert("Sorry, could not display attachment. Internal error (Modal init error).");
            }
        } else {
            console.error(`>>> Modal element ${targetModalSelector} not found in DOM upon click.`);
            alert("Sorry, could not display attachment. Modal element not found.");
        }
    });
});

function loadTestCase(test_run_id, test_case_id) {
    $(testCaseAreaLocator).load(`/trc/${test_run_id}/${test_case_id}`, function (responseTxt, statusTxt, xhr) {
        if (statusTxt == "success") {

            const attachmentModal = $('#attachmentModal');
            const modalAttachmentContent = $('#modalAttachmentContent');

            if (attachmentModal.length) {
                try {

                    attachmentModal.off('show.bs.modal').on('show.bs.modal', function (event) {
                        const triggerElement = $(event.relatedTarget);
                        const attachmentId = triggerElement.data('attachment-id');
                        modalAttachmentContent.empty().html('<p class="text-center">Loading...</p>');

                        $.ajax({
                            url: `/get-attachment-modal-content/${attachmentId}`,
                            method: 'GET',
                            success: function (htmlResponse) {
                                if (htmlResponse && typeof htmlResponse === 'string' && htmlResponse.trim().length > 0) {
                                    modalAttachmentContent.empty().html(htmlResponse);

                                    modalAttachmentContent.find('video').each(function () { this.play(); });
                                } else {
                                    modalAttachmentContent.empty().html('<p class="text-warning text-center">Received empty content.</p>');
                                }
                            },
                            error: function (xhr, status, error) {
                                modalAttachmentContent.empty().html('<p class="text-danger text-center">Failed to load content.</p>');
                            }
                        });
                    });

                    attachmentModal.off('hidden.bs.modal').on('hidden.bs.modal', function (event) {
                        modalAttachmentContent.empty();
                    });

                } catch (e) {
                    console.error(">>> Failed to get or create Bootstrap Modal instance AFTER AJAX load:", e);
                    alert("Maaf, inisialisasi modal lampiran gagal.");
                }
            } else {
                console.error(">>> Modal element #attachmentModal NOT found within loaded content!");
            }

        } else {
            console.error("Error loading test case content:", statusTxt, xhr);
            $(testCaseAreaLocator).html('<p class="text-danger text-center">Failed to load test case content.</p>');
        }
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
    const TODO = 4;
    const SKIPPED = 5;
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
            // Existing result badge updates
            if (status == 1) {
                $(`.result_badge[data-test_case_id='${test_case_id}']`).html('<span class="badge bg-success">Passed</span>');
            } else if (status == 2) {
                $(`.result_badge[data-test_case_id='${test_case_id}']`).html('<span class="badge bg-danger">Failed</span>');
            } else if (status == 3) {
                $(`.result_badge[data-test_case_id='${test_case_id}']`).html('<span class="badge bg-warning">Blocked</span>');
            } else if (status == 4) {
                $(`.result_badge[data-test_case_id='${test_case_id}']`).html('<span class="badge bg-secondary">To Do</span>');
            } else if (status == 5) {
                $(`.result_badge[data-test_case_id='${test_case_id}']`).html('<span class="badge bg-info">Skipped</span>');
            }

            // Update the status badge
            const badge = document.getElementById(`status-badge-${test_case_id}`);
            if (badge) {
                // Remove all existing background classes
                badge.classList.remove('bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-secondary');

                // Add the appropriate class based on the new status
                switch (status) {
                    case 1: // PASSED
                        badge.classList.add('bg-success');
                        break;
                    case 2: // FAILED
                        badge.classList.add('bg-danger');
                        break;
                    case 3: // BLOCKED
                        badge.classList.add('bg-warning');
                        break;
                    case 4: // TODO
                        badge.classList.add('bg-secondary');
                        break;
                    case 5: // SKIPPED
                        badge.classList.add('bg-info');
                        break;
                }
            }

            loadChart(test_run_id); // reload chart
        }
    });
}

function updateAssignee(userId, testCaseId, testRunId) {
    $.ajax({
        url: '/test-run/update-assignee',
        type: 'POST',
        data: {
            user_id: userId,
            test_case_id: testCaseId,
            test_run_id: testRunId
        },
        success: function (data) {
            if (data.success) {
                alert('Assignee updated!');
            } else {
                alert('Failed to update assignee.');
            }
        },
        error: function (xhr, status, error) {
            alert('Error updating assignee');
            console.error(error);
            console.error('test' + $data)
        }
    });
}

/*
   const PASSED = 1;
    const FAILED = 2;
    const BLOCKED = 3;
    const TODO = 4;
    const SKIPPED = 5;
 */

$('body').on('click', '.test_run_case_btn', function () {

    let status = $(this).attr('data-status');
    let test_run_id = $(this).attr('data-test_run_id');

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
        } else if (status == 5) {
            $(this).removeClass("btn-info");
            $(this).addClass("btn-outline-info");
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
    } else if (status == 5) {
        $(this).removeClass("btn-outline-info");
        $(this).addClass("btn-info");
    }

});


$('body').on('click', '.tree_test_case', function () {

    $('.tree_test_case.selected').removeClass("selected");

    $(this).addClass('selected');
})

function submitComment(test_run_id, test_case_id) {
    const comment = document.getElementById('comment').value.trim();

    if (!comment) {
        alert('Please enter a comment before submitting.');
        return;
    }

    let formData = new FormData();
    formData.append('comment', comment);
    const files = document.getElementById('files').files;
    const userId = document.querySelector('meta[name="user-id"]').content;

    formData.append('user_id', userId);
    formData.append('test_run_id', test_run_id);
    formData.append('test_case_id', test_case_id);

    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }

    showLoadingOverlay();
    console.log(">>> Showing loading overlay...");

    $.ajax({
        type: "POST",
        url: `/comment/${test_run_id}/${test_case_id}`,
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            document.getElementById('comment').value = '';
            document.getElementById('files').value = '';

            loadTestCase(test_run_id, test_case_id);

        },
        error: function (xhr, status, error) {
            console.error('Error details:', {
                status: xhr.status,
                responseText: xhr.responseText,
                error: error
            });
            alert(`Failed to submit comment: ${xhr.responseText || error}`);
        },
        complete: function () {
            hideLoadingOverlay();
            console.log(">>> Hiding loading overlay...");
        }
    });
}
