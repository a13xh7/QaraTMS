$(document).ready(function () {

    let tree = $("#tree");
    let treeData = [];
    const sortable = new TreeSortable();

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
     loadTree();


    const delay = () => {
        return new Promise(resolve => {
            setTimeout(() => {
                resolve();
            }, 1000);
        });
    };

    sortable.onSortCompleted(async (event, ui) => {
      //  console.log( ui.item.data('undefined'));
      //  console.log( ui.item.data('id'));
        console.log( ui.item.attr('data-undefined'));
       // console.log( ui.item.getParent().attr('data-undefined'));
        // console.log( ui.item.getParent().attr('data-parent_id'));


    });

    $(document).on("click", ".add-child", function (e) {
        e.preventDefault();
        $(this).addChildBranch();
        console.log(  $(this).parent().parent().parent().parent().data('undefined'));
    });

    $(document).on("click", ".add-sibling", function (e) {
        e.preventDefault();
        $(this).addSiblingBranch();
    });

    $(document).on("click", ".remove-branch", function (e) {
        e.preventDefault();

        const confirm = window.confirm("Are you sure you want to delete this branch?");
        if (!confirm) {
            return;
        }

        $(this).removeBranch();
    });
});
