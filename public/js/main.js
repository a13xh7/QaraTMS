/****************************************************************************
 * LIGHTBOX - Show lightbox for resized image
 *****************************************************************************/

$("body").on( "click", "#test_case_content img, .document img", function() {
    var imageSrc =  $(this).attr('src');
    $("#any_img_lightbox_image").attr("src",imageSrc);
    $("#any_img_lightbox").modal('show');
});


/****************************************************************************
 * TEXTAREA RESIZE - steps and preconditions in case editor
 *****************************************************************************/

$.fn.autoResize = function(){
    let r = e => {
        e.style.height = '';
        e.style.height = e.scrollHeight + 'px'
    };
    return this.each((i,e) => {
        e.style.overflow = 'hidden';
        r(e);
        $(e).bind('input', e => {
            r(e.target);
        })
    })
};

$( document ).ready(function() {

    $('.alert').show('fade', 500);
    setTimeout(removeAlert, 3000);

    $('body').on('click', '.alert button', function() {
        removeAlert();
    });

    function removeAlert() {
        $('.alert').hide('fade', 500);
        setTimeout(function() {
            $('.alert').remove();
        }, 500);
    }
});

var testCaseJson; // и переменная должна быть глобальной
function loadTestCaseJson(id) {
    $.ajax({
        type: 'GET',
        url: '/test-case/get',
        async: false,  // без этого не будет работать return
        data: { id: id },

        success: function (data) {
            testCaseJson = $.parseJSON(data);
        }
    });
}

function sortSuitesByParentId2(repository_id) {
    var childSuiteHtml;

    $( $("[data-test_suite_id]") ).each(function(index) { console.log('sad')
        let parent_id = $(this).attr('data-parent_id'); // достать parent_id

        if(parent_id != repository_id ) {  // не ставить !== иначе все сломается
            // childSuiteHtml = "<ul>" + $(this).prop('outerHTML').toString() + "</ul>"; // сохранить код элемента
            // childSuiteHtml = "<li>" + $(this).prop('outerHTML').toString() + "</li>";
            childSuiteHtml =  $(this).prop('outerHTML').toString() ;
            $(this).remove();
            $(`[data-test_suite_id=${parent_id}]`).append(childSuiteHtml);
        }
    });
}

function sortTreeByParentId() {

    var childSuiteHtml;

    $( $(".tree_suite") ).each( function(index) {
        let parent_id = $(this).attr('data-parent_id'); // достать parent_id
        let parentSuiteLocator = `.tree_test_suite[data-test_suite_id="${parent_id}"]`

        childSuiteHtml = $(this).prop('outerHTML').toString();

        if($(parentSuiteLocator).length > 0 ) {
            $(parentSuiteLocator).append(childSuiteHtml);
            $(this).remove();
        }
    });
}


/****************************************************************************
 * REZIABLE for test case viewer
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
