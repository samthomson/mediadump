

var sCdnURL = "";

var oaQueries = [];

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
	if(oaQueries.length > 0){
		$.get("/api/search", {query:oaQueries[0].value}, function(results){
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

		console.log("olink: " + oLink);

		var sSingleTreeItem = "";

		var sDisplay = folderFromUniqueDir(oLink.value);
		var sValue = oLink.value;

		sSingleTreeItem +='<a class="tree_link col-xs-6 col-sm-4" href="javascript: setSolitaryQuery(\'' + sDisplay + '\', \'' + sValue + '\');" alt="' + sDisplay + '" title="' + sDisplay + '">';

		sSingleTreeItem +='<div class="tree_image_container">';
		sSingleTreeItem +='<img src="' + urlFromHash('medium', oLink, '') + '"/>';
		sSingleTreeItem +='</div>';
		sSingleTreeItem +='<span class="tree_link_title">' + sDisplay + '</span>';


		sSingleTreeItem +='</a>';


		htmlTree += sSingleTreeItem;
	});

	$("#browse_tree").html(htmlTree);
}

/*

UI EVENTS / LOGIC

*/
function setSolitaryQuery(sDisplay, sValue){
	var aaQuery = {};
	aaQuery["display"] = sDisplay;
	aaQuery["value"] = sValue;

	console.log("set query: " + aaQuery);
	oaQueries = Array();
	oaQueries.push(aaQuery);
	console.log(oaQueries);
	performSearch();
}