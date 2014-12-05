
// object array containing files from tree request
var oTree = [];

$( document ).ready(function() {
    // get tree

    // get header vars
});

function getTree(){
	$.get("/api/tree", function(results){
		oTree = results.data;
		renderTree();
	});
}
function renderTree()
{
	var htmlTree = "";

	oTree.forEach(function(oLink){

		var sSingleTreeItem = "";

		sSingleTreeItem +='<div class="tree_link col-xs-6 col-sm-4" ng-click="do_default_query(' + oLink.value + ')" alt="' + oLink.value + '" title="' + oLink.value + '">';

		sSingleTreeItem +='<div class="tree_image_container">';
		sSingleTreeItem +='<img ng-src="' + urlFromHash('medium', oLink, '') + '"/>';
		sSingleTreeItem +='</div>';
		sSingleTreeItem +='<span class="tree_link_title">' + folderFromUniqueDir(oLink.value) + '</span>';


		sSingleTreeItem +='</div>';


		htmlTree += sSingleTreeItem;
	});

	$("#browse_tree").html(htmlTree);
}