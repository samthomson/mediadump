
/* UI VARS */
var bLoading = false;
var sViewMode = "browse";

/* logic vars */
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
		//oTree = $.parseJSON(results);
		oTree = results;
		renderTree();
	});
}
function performSearch()
{
	if(oaQueries.length > 0){
		setLoading(true);
		$.get("/api/search", {query:oaQueries[0].value}, function(results){
			oResults = results.results;
			oResultsData = results.info;
			setLoading(false);
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
}
/*

BUILD UI

*/
function renderTree()
{
	var htmlTree = "";


	oTree.forEach(function(oLink){

		var sSingleTreeItem = "";

		var sValue = sLinkSafeJSString(oLink.value);
		var sDisplay = folderFromUniqueDir(sValue);

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

LOGIC

*/
function setSolitaryQuery(sDisplay, sValue){

	var aaQuery = {};
	aaQuery["display"] = sDisplay;
	aaQuery["value"] = sValue;
	
	oaQueries = Array();
	oaQueries.push(aaQuery);
	performSearch();
}

/*

UI EVENTS

*/

function setLoading(bLoadingNew){	
	if(bLoading != bLoadingNew){
		bLoading = bLoadingNew;
		updateLoading();
	}	
}
function updateLoading(){
	if(bLoading){
		$("#loading").show();
	}else{
		$("#loading").hide();
	}
}

function setLoading(bLoadingNew){	
	if(bLoading != bLoadingNew){
		bLoading = bLoadingNew;
		updateLoading();
	}	
}
function updateLoading(){
	if(bLoading){
		$("#loading").show();
	}else{
		$("#loading").hide();
	}
}

function setViewMode(sNewViewMode){	
	if(sViewMode != sNewViewMode){
		sViewMode = sNewViewMode;
		updateViewMode();
	}	
}
function updateViewMode(){
	switch(sViewMode)
	{
		case "results":
			break;
		case "map":
			break;
		// browse
		default:
			break;

	}
}

sViewMode

/*

BOILERPLATE HELPER FUNCTIONS

*/
function sLinkSafeJSString(sString)
{
	return sString.replace(/[/\\*]/g, "\\\\");
}