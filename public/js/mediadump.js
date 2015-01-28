
/* UI VARS */
var bLoading = false;
var bLightboxShowing = false;
var sSearchMode = "browse";
var bInfoShowing = false;

var gmapMap = null;
var rtime = new Date(1, 1, 2000, 12,00,00);
var timeout = false;
var delta = 200;
var oUITags = null;
var lastValue = '';

var htmlAutoComplete = '';

var xhrSearch;
var xhrSuggest;
var xhrFileInfo;

/* logic vars */
var bQueryInputEventsOn = true;

var sCdnURL = "http://mediadump.samt.st";

var oaQueries = [];
var iPage = 1;
var iFile = -1;

var oTree = [];

var oResults = [];
var oResultsData = [];
var oaMarkers = [];
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
	closeLightbox();
	if(oaQueries.length > 0){
		setLoading(true);
		if(xhrSearch && xhrSearch.readystate != 4){
            xhrSearch.abort();
        }

        var sQueryValue = "";

        oaQueries.forEach(function(oObj, cIndex){
        	sQueryValue += oObj.value;

        	if(cIndex != (oaQueries.length - 1))
        	{
        		sQueryValue += "|";
        	}
        });

    	xhrSearch = $.get("/api/search", {query:decodeURIComponent(sQueryValue), page: iPage}, function(results){
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


	var s_current_row = '';
	var i_max_row_width = $("#thumb_results").innerWidth() - 17; // 17
	console.log("max row width: " + i_max_row_width);
	var i_running_row_width = 0;
	var i_base_height = i_default_height;
	var i_files_in_row = 0;
	var i_row_margin_cumu = 0;
	
	
	var i_file_limit = 100;
	var i_loaded = 0;
	var i_default_height = 300;
	var i_margin = 4;
	var sIMG = '';

	i_base_height = i_default_height;
	
	while (oaMarkers.length > 0) {
	    oaMarkers.pop().setMap(null);
	}
	oaMarkers.length = 0;


	if(oResults.length > 0){
		
		oResults.forEach(function(oFile, cIndex){
			//
			// thumbs
			//
			var sSingleFileItem = "";

			sSingleFileItem +='<a class="thumb_result_link" onmousedown="preloadThumb('+cIndex+')" href="javascript:thumbClick('+cIndex+');">';

			sConfidenceClass = "";
			if(oFile.confidence < 40){
				sConfidenceClass = "less-confident";
			}	
			if(oFile.confidence < 20){
				sConfidenceClass = "least-confident";
			}	
			sSingleFileItem +='<div class="tree_image_container justify-thumbnail">';
			sSingleFileItem +='<img src="' + urlFromHash('medium', oFile.hash, '') + '" id="' + oFile.id + '" class="result-thumb ' + sConfidenceClass + '" />';


			sSingleFileItem +='</div>';
			sSingleFileItem +='</a>';


			s_current_row += sSingleFileItem;

			i_running_row_width += (parseInt(oFile.width) + i_margin);
			i_row_margin_cumu += i_margin;

			if(i_running_row_width > i_max_row_width || iFile == (oResults.length-1)){
				var i_overlap_ratio = (i_max_row_width - i_row_margin_cumu) / (i_running_row_width - i_row_margin_cumu);
				var i_height = i_overlap_ratio * i_base_height;
				if(sSearchMode === 'mapsearch-mode'){
					i_height = 121;
				}	
				// finish the row
				console.log("finishing the row, running width: "+i_running_row_width+", height: "+i_height);
				s_current_row = '<div class="justify-row" style="height:'+i_height+'px;">' + s_current_row + '</div>';
				//$("#thumb_results").append(s_current_row);
				htmlThumbs += s_current_row;
				// start next
				s_current_row = '';
				i_base_height = i_default_height;
				i_running_row_width = 0;
				i_files_in_row = 0;
				i_row_margin_cumu = 0;
			}
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
						icon: image
					});

					google.maps.event.addListener(beachMarker, "click", function() {
					    thumbClick(cIndex);
					});
					oaMarkers.push(beachMarker);


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
					oaMarkers.push(beachMarker);

					google.maps.event.addListener(beachMarker, "click", function() {
					    thumbClick(cIndex);
					});
				}
			}
		});
	}else{
		// no results
		htmlThumbs = '<div id="no-results" class="centred-message">no results..</div>';
	}
	$("#thumb_results").html(htmlThumbs)
}

function renderPagination(){
	// remove pagination
	$("#map_pagination").html("");
	$("#grid_pagination").html("");

	var sPagination = "";

	if(oResults.length > 0){
		// build pagination
		
		var sShowing = "<span>showing " + oResultsData.lower + " - " + oResultsData.upper + " / " + oResultsData.count + '</span>';

		sShowing += '<span><i class="glyphicon glyphicon-flash"></i> found in ~' + parseInt(oResultsData.speed) +' ms</span>';

		if(sSearchMode == "browse"){
			if(iPage > 1){
				sPagination += '<a class="btn active pull-left btn-xs" href="javascript:setPage(' + (iPage - 1) + ');"><i class="glyphicon glyphicon-chevron-left"></i> previous</a>';
			}

			if(iPage < oResultsData.available_pages){
				sPagination += '<a class="pull-right btn active btn-xs" ng-show="page < (result_info.available_pages)" href="javascript:setPage(' + (iPage + 1) + ');">next <i class="glyphicon glyphicon-chevron-right"></i></a>';
			}
		}
		sPagination += sShowing;
	}

	if(sSearchMode == "map"){
		$("#map_pagination").html(sPagination);			
	}else{
		$("#grid_pagination").html(sPagination);
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

function addQuery(sDisplay, sValue, bUpdateSearchInput){
	// by default silently update the searchinput
	bUpdateSearchInput = typeof bUpdateSearchInput !== 'undefined' ? bUpdateSearchInput : true;
	// called as a result of tag add event
	var aaQuery = {};
	aaQuery["display"] = sDisplay;
	aaQuery["value"] = sValue;
	
	oaQueries.push(aaQuery);
	performSearch();

	if(bUpdateSearchInput){
		bQueryInputEventsOn = false;
		// tags already in ui
		var saTags = oUITags.getTags();

		oaQueries.forEach(function(oQuery){
			// don't add if already there
			if(!oUITags.hasTag(oQuery["display"])){
				oUITags.addTag(String(oQuery["display"]));
			}

		});
		bQueryInputEventsOn = true;
	}
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
function removeQueryFromModelAndUI(sDisplayTag){
	log("remove tag: " + sDisplayTag);

	for(var rcQueryIndex = oaQueries.length -1; rcQueryIndex > -1; rcQueryIndex--){
		if(oaQueries[rcQueryIndex].display == sDisplayTag)
		{
			oaQueries.splice(rcQueryIndex, 1);
		}
	}

	
	performSearch();
	queryChange();
}
function emptyQueries(){
	removeAllQueriesFromModelAndUI();
	queryChange();
}
function pushVarsIntoURL(){
	// queries, page, file
	var sHash = "";

	var aaVars = [];
	/*
	if(oaQueries.length > 0)
	{
		oaQueries.forEach(oQuery){
			aaVars[]oQuery.display
		}
	}
	*/
}
/*

MODEL EVENTS

*/

function setAutoComplete(htmlAutoCompleteNew){	
	if(htmlAutoComplete != htmlAutoCompleteNew){
		htmlAutoComplete = htmlAutoCompleteNew;
		updateAutoComplete();
	}	
}
function updateAutoComplete(){
	$("#autocomplete").html(htmlAutoComplete);
}

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
	if(bInfoShowing){
		updateFileInfo();
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
		resetDataAndUI();
	}
}


function silentAddTag(sTagDisplay){
	// puts a display tag into search input without it triggering events
	oUITags.addTag(sTagDisplay);
}
function resetDataAndUI(){
	// reset data
	iPage = 1;
	oaQueries = [];
	oResults = [];


	// reset ui?
	renderPagination();
	$("#thumb_results").hide();
	sizeDivide();
	$("#browse_tree").show();
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
	setUIMode("shuffle");
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
	$("#lightbox_info_view").html('');
}
function lightChange(iOffset){
	iNewIndex = iFile + iOffset;
	$("#lightbox_info_view").html('');
	if(iNewIndex < 0){
		iNewIndex = oResults.length - 1;
	}
	if(iNewIndex > (oResults.length -1)){
		iNewIndex = 0;
	}
	setFile(iNewIndex);	
}
function toggleInfo(){
	bInfoShowing = !bInfoShowing;
	if(!bInfoShowing){
		// hide info container
		$("#lightbox_info_view").html('');
		$("#lightbox").removeClass("with-info");
	}else{
		// trigger info call with content update
		updateFileInfo();
		$("#lightbox").addClass("with-info");
	}
}
function updateFileInfo(){
	// ajax call and dump data into relevant div
	if(xhrSearch && xhrSearch.readystate != 4){
        xhrSearch.abort();
    }

    if(iFile > -1){
		xhrFileInfo = $.get("/view/filedata",{hash: oResults[iFile].hash}, function(results){
			$("#lightbox_info_view").html(results);
		});
	}
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
function setSolitaryQuery(sDisplay, sValue){
	// add query, triggering a search, and new tag for search input
	
	var aaQuery = {};
	aaQuery["display"] = sDisplay;
	aaQuery["value"] = sValue;
	
	bQueryInputEventsOn = false;
	removeAllQueriesFromModelAndUI();
	bQueryInputEventsOn = true;
	
	//oaQueries.push(aaQuery);

	addQuery(sDisplay, sValue);
	//silentAddTag(sDisplay);

	performSearch();
	queryChange();	
}
function removeAllQueriesFromModelAndUI(){
	oaQueries = [];
	var saTags = oUITags.getTags();

	for (i = saTags.length - 1; i > -1; i--){
		oUITags.removeTag(saTags[i]);
	}
}
function autoSuggestSelect(sDisplay, sValue){
	// remove current text in input
	$("#search-input input").val(''); // this should trigger events which close the drop down to
	setAutoComplete('');
	// add tag
	addQuery(sDisplay, sValue);
}
function addedFromInput(sDisplay){
	// add the query, triggering a search
	addQuery(sDisplay, sDisplay, false);
}

$( document ).ready(function() {
    // get tree
    getTree();
    // get header vars

    
	oUITags = $('#search-input').tags({
	    readOnly: false,
	    tagClass: "search-tag",
	    promptText: "search..",
	    afterAddingTag: function(tag){ 
	    	if(bQueryInputEventsOn){
	    		addedFromInput(tag);
	    	}
		},
		beforeDeletingTag: function(tag){
	    	if(bQueryInputEventsOn){
	    		removeQueryFromModelAndUI(tag);
	    	}
		}
	});

	$("#search-input input").on('change keyup paste', function(event){
		if ($(this).val() != lastValue) {
	        lastValue = $(this).val();
	        if(lastValue == ""){
	        	// hide it
	        	setAutoComplete('');
	        }else{
	        	if(xhrSuggest && xhrSuggest.readystate != 4){
		            xhrSuggest.abort();
		        }
	        	xhrSuggest = $.get("/api/dbsuggest",
	        		{match: lastValue}, 
	        		function(results){
	        			var htmlAutoComplete = "";


	        			results["suggestions"].forEach(function(oResult, cCount){

							var sValue = sLinkSafeJSString(oResult.value);
							var sDisplay = folderFromUniqueDir(sValue);
		
							var sMatchText = sValue.replace(lastValue,'<strong>'+lastValue+'</strong>')

	        				htmlAutoComplete += '<a href="javascript:autoSuggestSelect(\'' + sDisplay + '\', \'' + sValue + '\');" class="auto-suggestion">';
	        				htmlAutoComplete += '<img src="' + urlFromHash("icon", oResult.hash, "") + '" />';
	        				htmlAutoComplete += sMatchText;
	        				htmlAutoComplete += '</a>';
	        			});

	        			setAutoComplete(htmlAutoComplete);
					}
				);
	        }
	    }
	});

	$("#search-input input").focus();


	// initial set up
	initializeGoogleMap();
	sizeDivide();
	setMode("browse");
});
/*

BOILERPLATE HELPER FUNCTIONS

*/
function sLinkSafeJSString(sString)
{
	sString = sString.replace(/\\/g, "\\\\");
	return sString;
}
function log(s){
	console.log(s);
}
function initializeGoogleMap() {
	var mapCanvas = document.getElementById('map-canvas');
	var mapOptions = {
		center: new google.maps.LatLng(0, 0),
		zoom: 2,
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