

var sCdnURL = "";

var saQueries = [];

var oTree = [];

var oResults = [];
var oResultsData = [];


$( document ).ready(function() {
    // get tree
    getTree();
    // get header vars
});

/*

GET DATA

*/
function getTree(){
	$.get("/api/tree", function(results){
		oTree = results;
		renderTree(oTree);
	});
}
function performSearch()
{
	if(saQueries.length > 0){
		$.get("/api/search", {query:saQueries[0]}, function(results){
			console.log(results.results);
			console.log(results.info);
			oResults = results.results;
			oResultsData = results.info;
			renderTree(oTree);
		});
	}
}

/*

FORMAT DATA

*/
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
function sFilterQuery(sQuery){
	return sQuery.toLowerCase();
	return encodeURIComponent(sQuery.toLowerCase());
}
/*

BUILD UI

*/
function renderTree(oTree)
{
	var htmlTree = "";

	oTree.forEach(function(oLink){

		var sSingleTreeItem = "";

		sSingleTreeItem +='<a class="tree_link col-xs-6 col-sm-4" href="javascript: setSolitaryQuery(\'' + encodeURIComponent(oLink.value) + '\');" alt="' + oLink.value + '" title="' + oLink.value + '">';

		sSingleTreeItem +='<div class="tree_image_container">';
		sSingleTreeItem +='<img src="' + urlFromHash('medium', oLink, '') + '"/>';
		sSingleTreeItem +='</div>';
		sSingleTreeItem +='<span class="tree_link_title">' + folderFromUniqueDir(oLink.value) + '</span>';


		sSingleTreeItem +='</a>';


		htmlTree += sSingleTreeItem;
	});

	$("#browse_tree").html(htmlTree);
}

/*

UI EVENTS / LOGIC

*/
function setSolitaryQuery(sQuery){
	console.log("set query: " + sQuery);
	saQueries = [sFilterQuery(sQuery)];
	console.log(saQueries);
	performSearch();
}