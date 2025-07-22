// Set up CSRF token for AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

/****************************************************************************
 * Document Ready Handler
 *****************************************************************************/
$(document).ready(function () {
    $('.alert').show('fade', 500);
    setTimeout(removeAlert, 3000);

    $('body').on('click', '.alert button', function () {
        removeAlert();
    });

    // Initialize Bootstrap dropdowns
    if (typeof bootstrap !== 'undefined') {
        var dropdownElementList = [].slice.call(document.querySelectorAll('[data-bs-toggle="dropdown"]'));
        dropdownElementList.forEach(function (dropdownToggleEl) {
            new bootstrap.Dropdown(dropdownToggleEl);
        });
    }

    // Helper function to close dropdowns
    function closeDropdowns($scope) {
        $scope.find('.sidebar-dropdown-menu').slideUp(300);
        $scope.find('.sidebar-dropdown-icon').removeClass('rotate-dropdown');
    }

    // Helper function to toggle dropdown
    function toggleDropdown($dropdownMenu, $dropdownIcon) {
        $dropdownMenu.slideToggle(300);
        $dropdownIcon.toggleClass('rotate-dropdown');

        // Update ARIA attributes
        const isExpanded = $dropdownIcon.hasClass('rotate-dropdown');
        $dropdownIcon.closest('.sidebar-dropdown-toggle').attr('aria-expanded', isExpanded);
    }

    // Initialize ARIA attributes for dropdowns
    $('.sidebar-dropdown-toggle').each(function () {
        const $toggle = $(this);
        const dropdownId = $toggle.next('.sidebar-dropdown-menu').attr('id') ||
            'dropdown-' + Math.random().toString(36).substr(2, 9);

        $toggle.attr({
            'role': 'button',
            'aria-expanded': 'false',
            'aria-controls': dropdownId,
            'tabindex': '0'
        });

        $toggle.next('.sidebar-dropdown-menu').attr('id', dropdownId);
    });

    // Sidebar dropdown functionality
    $('.sidebar-dropdown-toggle').on('click', function (e) {
        e.stopPropagation(); // Prevent event bubbling

        const $dropdownMenu = $(this).next('.sidebar-dropdown-menu');
        const $dropdownIcon = $(this).find('.sidebar-dropdown-icon');
        const $parentDropdown = $(this).closest('.sidebar-dropdown');

        // If clicking a nested dropdown
        if ($parentDropdown.parent().hasClass('sidebar-dropdown-menu')) {
            // Close sibling dropdowns at the same level
            closeDropdowns($parentDropdown.siblings('.sidebar-dropdown'));

            // Toggle only this dropdown
            toggleDropdown($dropdownMenu, $dropdownIcon);
        } else {
            // For top-level dropdowns, close all other top-level dropdowns
            closeDropdowns($('.sidebar-dropdown').not($parentDropdown));

            // Toggle this dropdown
            toggleDropdown($dropdownMenu, $dropdownIcon);
        }
    });

    // Handle keyboard navigation
    $('.sidebar-dropdown-toggle').on('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).trigger('click');
        }
    });

    // Close dropdowns when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.sidebar-dropdown').length) {
            closeDropdowns($('.sidebar-dropdown'));
        }
    });

    // Initially hide all dropdown menus
    $('.sidebar-dropdown-menu').hide();
});

/****************************************************************************
 * Alert Functions
 *****************************************************************************/
function removeAlert() {
    $('.alert').fadeOut(500, function () {
        $(this).remove();
    });
}

/****************************************************************************
 * LIGHTBOX - Show lightbox for resized image
 *****************************************************************************/
$("body").on("click", "#test_case_content img, .document img", function () {
    var imageSrc = $(this).attr('src');
    $("#any_img_lightbox_image").attr("src", imageSrc);
    $("#any_img_lightbox").modal('show');
});

/****************************************************************************
 * TEXTAREA RESIZE - steps and preconditions in case editor
 *****************************************************************************/
$.fn.autoResize = function () {
    let r = e => {
        e.style.height = '';
        e.style.height = e.scrollHeight + 'px'
    };
    return this.each((i, e) => {
        e.style.overflow = 'hidden';
        r(e);
        $(e).bind('input', e => {
            r(e.target);
        })
    })
};

/****************************************************************************
 * Test Case Functions
 *****************************************************************************/
var testCaseJson; // Global variable for test case data

function loadTestCaseJson(id) {
    $.ajax({
        type: 'GET',
        url: '/test-case/get',
        async: false,
        data: { id: id },
        success: function (data) {
            testCaseJson = $.parseJSON(data);
        }
    });
}

/****************************************************************************
 * Suite and Tree Functions
 *****************************************************************************/
function sortSuitesByParentId2(repository_id) {
    var childSuiteHtml;

    $($("[data-test_suite_id]")).each(function (index) {
        let parent_id = $(this).attr('data-parent_id');

        if (parent_id != repository_id) {
            childSuiteHtml = $(this).prop('outerHTML').toString();
            $(this).remove();
            $(`[data-test_suite_id=${parent_id}]`).append(childSuiteHtml);
        }
    });
}

function sortTreeByParentId() {
    var childSuiteHtml;

    $($(".tree_suite")).each(function (index) {
        let parent_id = $(this).attr('data-parent_id');
        let parentSuiteLocator = `.tree_test_suite[data-test_suite_id="${parent_id}"]`

        childSuiteHtml = $(this).prop('outerHTML').toString();

        if ($(parentSuiteLocator).length > 0) {
            $(parentSuiteLocator).append(childSuiteHtml);
            $(this).remove();
        }
    });
}

/****************************************************************************
 * Utility Functions
 *****************************************************************************/
function openInNewWindow(url) {
    window.open(url, '_blank');
}

/****************************************************************************
 * RESIZABLE for test case viewer
 *****************************************************************************/
//
// interact('.resizable')
//     .resizable({
//         edges: { top: false, left: true, bottom: false, right: false },
//         listeners: {
//             move: function (event) {
//                 let { x, y } = event.target.dataset
//
//                 x = (parseFloat(x) || 0) + event.deltaRect.left
//                // y = (parseFloat(y) || 0) + event.deltaRect.top
//
//                 Object.assign(event.target.style, {
//                     width: `${event.rect.width}px`,
//                     // height: `${event.rect.height}px`,
//                     // transform: `translate(${x}px, ${y}px)`
//                 })
//
//                 Object.assign(event.target.dataset, { x })
//             }
//         }
//     })


//Date
function validateDates() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;
    const error = document.getElementById('date-error');
    if (start && end && start > end) {
        error.style.display = 'block';
    } else {
        error.style.display = 'none';
    }
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function setDateRange(range) {
    const today = new Date();
    let start, end = new Date(today);

    switch (range) {
        case 'last-month':
            end = new Date(today);
            start = new Date(today);
            start.setMonth(start.getMonth() - 1);
            break;
        case 'last-3-months':
            end = new Date(today);
            start = new Date(today);
            start.setMonth(start.getMonth() - 3);
            break;
        case 'last-6-months':
            end = new Date(today);
            start = new Date(today);
            start.setMonth(start.getMonth() - 6);
            break;
        case 'last-year':
            end = new Date(today);
            start = new Date(today);
            start.setFullYear(start.getFullYear() - 1);
            break;
        default:
            return;
    }
    document.getElementById('start_date').value = formatDate(start);
    document.getElementById('end_date').value = formatDate(end);
}

function showLoadingOverlay() {
    $('#loading-overlay').show();
}

function hideLoadingOverlay() {
    $('#loading-overlay').hide();
}
