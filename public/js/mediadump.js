
/* UI VARS */
var bLoading = false;
var sSearchMode = "browse";

var gmapMap = null;


/* logic vars */


var sCdnURL = "";

var oaQueries = [];
var iPage = 1;

var oTree = [];

var oResults = [];
var oResultsData = [];



var tagSuggestions = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: '/api/suggest/?match=%QUERY'
});
tagSuggestions.initialize();

/**/



$( document ).ready(function() {
    // get tree
    getTree();
    // get header vars

    /*
	$('#search-input').tags({
	    readOnly: false,
	    tagClass: "search-tag",
	    beforeAddingTag: function(tag){ 
	    	addQuery(tag, tag);
	    	$('#search-input').renameTag("tag "+tag, tag+"fdsf")
	    	log(tag);
		}
	});
	*/


	var oSearchInput = $('#search-input');


	oSearchInput.tagsinput({
		tagClass: "search-tag",
		itemValue: "value",
		typeaheadjs: {
			name: 'tagSuggestions',
			source: tagSuggestions.ttAdapter(),

		}
	});

	oSearchInput.on('itemAdded', function(event) {
	  	addQuery(event.item.value, event.item.value);
	});

	oSearchInput.on('itemRemoved', function(event) {
	  	removeQuery(event.item.value);
	});

	initializeGoogleMap();
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
		$.get("/api/search", {query:oaQueries[0].value, page: iPage}, function(results){
			oResults = results.results;
			oResultsData = results.info;
			setLoading(false);

			renderResults();
			renderPagination();
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

function urlFromHash(sMode, hash, sExt){	
	switch(sMode){
		case 'lightbox':
			return sCdnURL + '/thumbs/large/'+hash+'.jpg';
			break;
		case 'icon':
			return sCdnURL + '/thumbs/icon/'+hash+'.jpg';
			break;
		case 'medium':
			return sCdnURL + '/thumbs/medium/'+hash+'.jpg';
			break;
		case 'small':
			return sCdnURL + '/thumbs/small/'+hash+'.jpg';
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
		sSingleTreeItem +='<img src="' + urlFromHash('medium', oLink.hash, '') + '"/>';
		sSingleTreeItem +='</div>';
		sSingleTreeItem +='<span class="tree_link_title">' + sDisplay + '</span>';


		sSingleTreeItem +='</a>';


		htmlTree += sSingleTreeItem;
	});

	$("#browse_tree").html(htmlTree);
}

function renderResults(){

	var htmlThumbs = "";

	if(oResults.length > 0){
		// there are results, display them
		oResults.forEach(function(oFile, cIndex){
			var sSingleFileItem = "";

			sSingleFileItem +='<a class="thumb_result_link" mousedown="preload_thumb('+cIndex+')" href="javascript:thumb_click('+cIndex+');">';

			sSingleFileItem +='<div class="tree_image_container">';
			sSingleFileItem +='<img src="' + urlFromHash('small', oFile.hash, '') + '" id="' + oFile.id + '"/>';


			sSingleFileItem +='</a>';

			htmlThumbs += sSingleFileItem;
		});
	}else{
		// no results
		htmlThumbs = "no results :(";
	}
	$("#thumb_results").html(htmlThumbs)
}

function renderPagination(){
	// remove pagination
	$("#map_pagination").html("");
	$("#grid_pagination").html("");


	if(oResults.length > 0){
		// build pagination
		var sPagination = "";
		var sShowing = "<span>showing " + oResultsData.lower + " - " + oResultsData.upper + " / " + oResultsData.count + '</span>';

		if(sSearchMode == "browse"){

			if(iPage > 1){
				sPagination += '<a class="btn active pull-left" href="javascript:setPage(' + iPage - 1 + ');"><i class="glyphicon glyphicon-chevron-left"></i> previous</a>';
			}

			if(iPage < oResultsData.available_pages){
				sPagination += '<a class="pull-right btn active" ng-show="page < (result_info.available_pages)" href="javascript:setPage(' + iPage - 1 + ');">next <i class="glyphicon glyphicon-chevron-right"></i></a>';
			}

			sPagination += sShowing;

			sPagination += '<span><i class="glyphicon glyphicon-flash"></i> found in ' + oResultsData.speed +' ms</span>';


			$("#grid_pagination").html(sPagination);
		}
		if(sSearchMode == "map"){

			sPagination += sShowing;


			$("#map_pagination").html(sPagination);
		}
	}
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
	queryChange();
}
function addQuery(sDisplay, sValue){

	// called as a result of tag add event
	var aaQuery = {};
	aaQuery["display"] = sDisplay;
	aaQuery["value"] = sValue;
	
	oaQueries.push(aaQuery);
	performSearch();
	queryChange();
}
function addQueryFromMap(){

	var llBounds = gmapMap.getBounds();

	var llNorthEast = llBounds.getNorthEast();
	var llSouthWest = llBounds.getSouthWest();


	var sQuery = "map=";
	sQuery += llSouthWest.lat().toFixed(2);
	sQuery += ",";			
	sQuery += llNorthEast.lat().toFixed(2);
	sQuery += ",";			
	sQuery += llSouthWest.lng().toFixed(2);
	sQuery += ",";			
	sQuery += llNorthEast.lng().toFixed(2);

	setSolitaryQuery("map search", sQuery);
}
function removeQuery(sDisplayTag){

	// find the query with matching display and remove it
	oaQueries.forEach(function(oQuery, cIndex){
		if(oQuery.display == sDisplayTag)
		{
			oaQueries.splice(cIndex, 1);
		}
	});
	
	performSearch();
	queryChange();
}

/*

MODEL EVENTS

*/

function setLoading(bLoadingNew){	
	$("#thumb_results").html('');;
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

function setSearchMode(sNewSearchMode){	
	if(sSearchMode != sNewSearchMode){
		sSearchMode = sNewSearchMode;
		updateSearchMode();
		evaluateBrowseOrResults();
	}	
}
function updateSearchMode(){
	// clear previous
	$("#browse_tree").hide();
	$("#thumb_results").hide();

	$("search_map").html('');
	$("thumb_results").html('');

	switch(sSearchMode)
	{
		case "map":
			$(".left_position").width("45%");
			$("#thumb_results").show();
			break;
		// browse
		default:
			$(".left_position").width("0%");
			$("#browse_tree").show();
			break;

	}
}
function setPage(iPageNew){	
	if(iPage != iPageNew){
		iPage = iPageNew;
		updatePage();
	}	
}
function updatePage(){
	performSearch();
}
function evaluateBrowseOrResults(){
	// if we're on browse mode either we show nav tree or thumb results if there are any
	if(sSearchMode == "browse"){
		$(".left_position").width("0%");
		$(".right_position").css("left", "0px");
		$("#thumb_results").show();


		if(oaQueries.length > 0){
			// queries, render results outcome
		}else{
			// no queries, show browse ui
			//$("#thumb_results").hide();
			//$("#browse_tree").show();
		}

	}else{
		// map
		$(".left_position").width("45%");
		$(".right_position").css("left", "50%");
		$("#thumb_results").show();
		$("#thumb_results").show();

		google.maps.event.trigger(gmapMap, "resize");

	}
}
function queryChange(){
	// if there are queries show thumb results
	if(oaQueries.length > 0){		
		$("#browse_tree").hide();
		$("#thumb_results").show();
	}else{
		$("#thumb_results").hide();
		$("#browse_tree").show();
	}
}


/*

UI EVENTS

*/
function setMode(sMode){
	$("#header-navigation li a").removeClass("active");

	setSearchMode(sMode);
	$("#header-navigation li a." + sMode + "-link").addClass("active");
}

/*

BOILERPLATE HELPER FUNCTIONS

*/
function sLinkSafeJSString(sString)
{
	return sString.replace(/[/\\*]/g, "\\\\");
}
function log(s){
	console.log(s);
}
function initializeGoogleMap() {
	var mapCanvas = document.getElementById('map-canvas');
	var mapOptions = {
		center: new google.maps.LatLng(0, 0),
		zoom: 1,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	}
	gmapMap = new google.maps.Map(mapCanvas, mapOptions)

	google.maps.event.addListener(gmapMap, 'idle', function() {
		// 3 seconds after the center of the map has changed, pan back to the
		// marker.
		// only fire if on map search mode
		if(sSearchMode == "map"){
			addQueryFromMap();
		}
	});
}	