
// object array containing files from tree request
var oTree = Array();
var sCdnURL = "";

$( document ).ready(function() {
    // get tree
    getTree();
    // get header vars
});

function getTree(){
	$.get("/api/tree", function(results){
		console.log(results);
		oTree = results;
		renderTree(oTree);
	});
}
function renderTree(oTree)
{
	var htmlTree = "";

	oTree.forEach(function(oLink){

		var sSingleTreeItem = "";

		sSingleTreeItem +='<div class="tree_link col-xs-6 col-sm-4" ng-click="do_default_query(' + oLink.value + ')" alt="' + oLink.value + '" title="' + oLink.value + '">';

		sSingleTreeItem +='<div class="tree_image_container">';
		sSingleTreeItem +='<img src="' + urlFromHash('medium', oLink, '') + '"/>';
		sSingleTreeItem +='</div>';
		sSingleTreeItem +='<span class="tree_link_title">' + folderFromUniqueDir(oLink.value) + '</span>';


		sSingleTreeItem +='</div>';


		htmlTree += sSingleTreeItem;
	});

	$("#browse_tree").html(htmlTree);
}


function folderFromUniqueDir(sDir){
	var sa = sDir.split("/");
		var iIndex = sa.length - 1;
		if(iIndex < 0)
			iIndex = 0;

		if(sa.length> 0)
			sDir = sa[iIndex];
		else
			sDir = "";


	sa = sDir.split("\\");
		var iIndex = sa.length - 1;
		if(iIndex < 0)
			iIndex = 0;

		if(sa.length> 0)
			return sa[iIndex];
		else
			return "";
}

function urlFromHash(sMode, oObject, sExt){
	if(typeof oObject === "undefined")
			return "";
		switch(sMode){
			case 'lightbox':
				return sCdnURL + '/thumbs/large/'+oObject.hash+'.jpg';
				break;
			case 'icon':
				return sCdnURL + '/thumbs/icon/'+oObject.hash+'.jpg';
				break;
			case 'medium':
				return sCdnURL + '/thumbs/medium/'+oObject.hash+'.jpg';
				break;
			case 'results':
				var sThumbSize = ($scope.search_input_mode === "browse") ? "small" : "small";
				return sCdnURL + '/thumbs/' + sThumbSize + '/'+oObject.hash+'.jpg';
				break;
		}
}