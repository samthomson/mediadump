
/* UI VARS */
var bLoading = false;
var bLightboxShowing = false;
var sSearchMode = "browse";

var gmapMap = null;
var rtime = new Date(1, 1, 2000, 12,00,00);
var timeout = false;
var delta = 200;

/* logic vars */


var sCdnURL = "";

var oaQueries = [];
var iPage = 1;
var iFile = -1;

var oTree = [];

var oResults = [];
var oResultsData = [];
var iStaggerMapIconLimit = 40;

var sMediaDumpColor = "#e74c3c";
var s_land = "#c0c0c0";
var s_media_dump_color = "#e74c3c";
var s_silver_colour = "#bdc3c7";
s_land = s_silver_colour;

var media_dump_map_options = {
		styles: [{
            "featureType": "water",
            "stylers": [{
                "color": "#ffffff"
            }]
        }, {
            "featureType": "landscape.natural",
            "stylers": [{
                "color": s_land
            }]
        }, {
            "featureType": "poi",
            "stylers": [{
                "visibility": "on"
            }, {
                "color": s_silver_colour
            }]
        }, {
            "featureType": "road",
            "stylers": [{
                "color": s_media_dump_color
            }]
        }, {
            "featureType": "poi",
            "elementType": "labels.text.stroke",
            "stylers": [{
                "color": "#ffffff"
            }]
        }, {
            "featureType": "poi",
            "elementType": "labels.text.fill",
            "stylers": [{
                "color": "#000000"
            }]
        }, {
            "featureType": "road",
            "elementType": "labels.text.stroke",
            "stylers": [{
                "color": s_silver_colour
            }]
        }, {
            "featureType": "road.local",
            "elementType": "labels.icon",
            "stylers": [{
                "color": "#000000"
            }]
        }, {
            "featureType": "transit",
            "stylers": [{
                "color": s_silver_colour
            }]
        }, {
            "featureType": "poi",
            "elementType": "labels.icon",
            "stylers": [{
                "color": "#000000"
            }]
        }, {
            "featureType": "water",
            "elementType": "labels.text.fill",
            "stylers": [{
                "color": s_silver_colour
            }]
        }, {
            "elementType": "labels.text.stroke",
            "stylers": [{
                "color": "#ffffff"
            }]
        }, {
            "featureType": "road.highway",
            "elementType": "labels.icon"
        }],
		backgroundColor: '#fff'};


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
			highlight: true,
		}
	});

	oSearchInput.on('itemAdded', function(event) {
	  	addQuery(event.item.value, event.item.value);
	});

	oSearchInput.on('itemRemoved', function(event) {
	  	removeQuery(event.item.value);
	});

	// initial set up
	initializeGoogleMap();
	sizeDivide();
	setMode("browse");
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


	oTree.forEach(function(oLink, cIndex){


		var sSingleTreeItem = "";

		var sValue = sLinkSafeJSString(oLink.value);
		var sDisplay = folderFromUniqueDir(sValue);

		sSingleTreeItem +='<a class="tree_link col-xs-6 col-sm-4" href="javascript: setSolitaryQuery(\'' + sDisplay + '\', \'' + sValue + '\');" alt="' + sDisplay + '" title="' + sDisplay + '">';

		sSingleTreeItem +='<div class="tree_image_container">';
		sSingleTreeItem +='<img src="' + urlFromHash('medium', oLink.hash, '') + '"/>';
		sSingleTreeItem +='</div>';
		sSingleTreeItem +='<span class="tree_link_title">' + sDisplay + '</span>';


		sSingleTreeItem +='</a>';

		// maybe start a row
		if(cIndex % 3 == 0)
			htmlTree += '<div class="row">';

		htmlTree += sSingleTreeItem;

		// maybe end a row
		if((cIndex + 1) % 3 == 0)
			htmlTree += '</div>';
	});

	$("#browse_tree").html(htmlTree);
}

function renderResults(){

	var htmlThumbs = "";

	if(oResults.length > 0){
		
		oResults.forEach(function(oFile, cIndex){
			//
			// thumbs
			//
			var sSingleFileItem = "";

			sSingleFileItem +='<a class="thumb_result_link" onmousedown="preloadThumb('+cIndex+')" href="javascript:thumbClick('+cIndex+');">';

			sSingleFileItem +='<div class="tree_image_container">';
			sSingleFileItem +='<img src="' + urlFromHash('small', oFile.hash, '') + '" id="' + oFile.id + '"/>';


			sSingleFileItem +='</div>';
			sSingleFileItem +='</a>';

			htmlThumbs += sSingleFileItem;
			//
			// thumbs
			//
			if(sSearchMode == "map"){
				// stagger results
				iMapIconModulus = 4; // 25%
				iMapPinModulus = 2; // 50%

				if(oResults.length < iStaggerMapIconLimit){
					iMapIconModulus = 1;
					iMapPinModulus = 10000;
				}

				if(cIndex % iMapIconModulus == 0){
					// image
					var image = urlFromHash('icon', oFile.hash, '');
					var myLatLng = new google.maps.LatLng(oFile.latitude, oFile.longitude);
					var beachMarker = new google.maps.Marker({
						position: myLatLng,
						map: gmapMap,
						icon: image,

					});

					google.maps.event.addListener(beachMarker, "click", function() {
					    thumbClick(cIndex);
					});


				}else if(cIndex % iMapPinModulus == 0){ // 20%
					// dot
					var myLatLng = new google.maps.LatLng(oFile.latitude, oFile.longitude);
					var beachMarker = new google.maps.Marker({
						position: myLatLng,
						map: gmapMap,
						icon: {
							path: google.maps.SymbolPath.CIRCLE,
							scale: 2,
							fillOpacity : 1,
							strokeColor: sMediaDumpColor,
							fillColor: sMediaDumpColor
						},

					});

					google.maps.event.addListener(beachMarker, "click", function() {
					    thumbClick(cIndex);
					});
				}
			}
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

		sShowing += '<span><i class="glyphicon glyphicon-flash"></i> found in ~' + parseInt(oResultsData.speed) +' ms</span>';

		if(sSearchMode == "browse"){

			if(iPage > 1){
				sPagination += '<a class="btn active pull-left" href="javascript:setPage(' + iPage - 1 + ');"><i class="glyphicon glyphicon-chevron-left"></i> previous</a>';
			}

			if(iPage < oResultsData.available_pages){
				sPagination += '<a class="pull-right btn active" ng-show="page < (result_info.available_pages)" href="javascript:setPage(' + iPage - 1 + ');">next <i class="glyphicon glyphicon-chevron-right"></i></a>';
			}

			sPagination += sShowing;

			


			$("#grid_pagination").html(sPagination);
		}
		if(sSearchMode == "map"){

			sPagination += sShowing;


			$("#map_pagination").html(sPagination);
		}
	}
}
function preloadThumb(cIndex){
	// if image or video?
	if(cIndex > -1 && cIndex < oResults.length){
		var imgPreload = new Image();
	    imgPreload.src = urlFromHash('lightbox', oResults[cIndex].hash, '');
	}
}
function preloadNeighbours(iIndex){
	var iaPreloadIndexes = [iFile - 1, iFile +1, iFile +2];

	iaPreloadIndexes.forEach(function(iIndex){
		preloadThumb(iIndex);
	});
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
function emptyQueries(){
	oaQueries = [];
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
function setLightShowing(bLightboxShowingNew){	
	if(bLightboxShowing != bLightboxShowingNew){
		bLightboxShowing = bLightboxShowingNew;
		updateLightShowing();
	}	
}
function updateLightShowing(){
	if(bLightboxShowing){
		// open lightbox
		$("#lightbox").show();
	}else{
		// stop any playing videos
		// close lightbox
		$("#lightbox").hide();
	}
}
function setFile(iFileIndex){	
	if(iFile != iFileIndex){
		iFile = iFileIndex;
		updateFile();
	}	
}
function updateFile(){
	if(iFile > -1){
		// render lightbox content
		$("#lightbox_contents img").attr("src", urlFromHash('lightbox', oResults[iFile].hash, ''));
		preloadNeighbours();
	}else{
		// strip lightbox content
		$("#lightbox_contents img").attr("src", "");
	}
}
function evaluateBrowseOrResults(){
	// if we're on browse mode either we show nav tree or thumb results if there are any
	sizeDivide();
	if(sSearchMode == "browse"){

		$("#thumb_results").hide();
		$("#browse_tree").hide();

		if(oaQueries.length > 0){
			// queries, render results outcome
			$("#thumb_results").show();
		}else{
			// no queries, show browse ui
			//$("#thumb_results").hide();
			$("#browse_tree").show();
		}

	}else{
		// map
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
		sizeDivide();
		$("#browse_tree").show();
	}
}


/*

UI EVENTS

*/
function home(){
	setMode("browse");
	emptyQueries();
}
function shuffle(){
	setSolitaryQuery("shuffle", "shuffle=random");
	setMode("browse");
	setUIMode("shuffle")
}
function setMode(sMode){
	setSearchMode(sMode);
	setUIMode(sMode);
}
function setUIMode(sMode){
	$("#header-navigation li a").removeClass("active");
	$("#header-navigation li a." + sMode + "-link").addClass("active");
}
function thumbClick(iIndex){
	// load lightbox stuff
	setFile(iIndex);
	// show lightbox
	setLightShowing(true);
}
function closeLightbox(){
	setLightShowing(false);
	setFile(-1);
}
function lightChange(iOffset){
	iNewIndex = iFile + iOffset;

	if(iNewIndex < 0){
		iNewIndex = oResults.length - 1;
	}
	if(iNewIndex > (oResults.length -1)){
		iNewIndex = 0;
	}

	thumbClick(iNewIndex);
}
$(window).resize(function() {
    rtime = new Date();
    if (timeout === false) {
        timeout = true;
        setTimeout(resizeend, delta);
    }
});

function resizeend() {
    if (new Date() - rtime < delta) {
        setTimeout(resizeend, delta);
    } else {
        timeout = false;
        sizeDivide();
    }               
}

function sizeDivide(){
	var iLeft = "45%";
	var iRightWidth = "50%";

	var iWidth = $("#main").width() - 16;
	var iThumbWidth = 125;
	var iThumbMargin = 4;

	if(sSearchMode == "map"){
		// map mode

		var iRightWidth = iWidth / 2;

		var iRightThumbs = Math.ceil(iRightWidth / (iThumbWidth + iThumbMargin));
		iRightWidth = iRightThumbs * (iThumbWidth + iThumbMargin);

		iLeftWidth = iWidth - iRightWidth - 8;


		$(".left_position").width(iLeftWidth);
		$(".right_position").css("left", iLeftWidth);

		initializeGoogleMap();
	}else{
		// thumbs only?	

		if(oaQueries.length == 0)	{
			// browse, set to full width
			$(".left_position").width("0%");
			$(".right_position").css("left", "0px");
		}else{
			// searching, set to dynamic
			var iRightWidth = iWidth;

			var iRightThumbs = Math.floor(iRightWidth / (iThumbWidth + iThumbMargin));
			iRightWidth = iRightThumbs * (iThumbWidth + iThumbMargin);

			iLeftWidth = iWidth - iRightWidth - 8;


			$(".left_position").width("0%");
			$(".right_position").css("left", (iLeftWidth/2));
		}

	}
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
		mapTypeId: google.maps.MapTypeId.ROADMAP,
    	backgroundColor: '#fff'
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

	gmapMap.setOptions({styles: media_dump_map_options.styles});

	//media_dump_map_options
}	