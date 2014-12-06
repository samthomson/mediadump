
/* UI VARS */
var bLoading = false;
var sSearchMode = "browse";

/* logic vars */
var sCdnURL = "";

var oaQueries = [];

var oTree = [];

var oResults = [];
var oResultsData = [];



var tagSuggestions = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  queryTokenizer: Bloodhound.tokenizers.whitespace,
	remote: '/api/suggest/?term=%QUERY'
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

	$("#search-select").select2({
    	placeholder: "Search..",
    	minimumInputLength: 1,
		width: "100%",
		ajax: {
			// instead of writing the function to execute the request we use Select2's convenient helper
	        url: "/api/suggest",
	        dataType: 'json',
	        quietMillis: 200,
	        data: function (term, page) {
	            return {
	                match: term, // search term
	            };
	        },
	        results: function (data, page) { // parse the results into the format expected by Select2.
	            // since we are using custom formatting functions we do not need to alter the remote JSON data
	            log(data);
	            return { results: data };
	        },
	        formatResult: function(item){
	        	return item.value;
	        },
	        cache: true
	    }
	});


/*

var oSearchInput = $('#search-input input');


oSearchInput.tagsinput({
	tagClass: "search-tag",
	itemValue: "value",
	itemValue: "value",
	typeaheadjs: {
		name: 'tagSuggestions',
		source: tagSuggestions.ttAdapter(),
	}
});

oSearchInput.on('itemAdded', function(event) {
  	addQuery(event.item, event.item);
});

*/



/*
	$( "#search-input input" ).autocomplete({
      source: "/api/suggest/",
      minLength: 1,
      delay: 200,
      select: function( event, ui ) {
        log( ui.item ?
          "Selected: " + ui.item.value + " aka " + ui.item.id :
          "Nothing selected, input was " + this.value );
      },
      select: function( event, ui ) {}
    });
*/



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

			renderResults();
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

function setSearchMode(sNewSearchMode){	
	if(sSearchMode != sNewSearchMode){
		sSearchMode = sNewSearchMode;
		updateSearchMode();
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
		default:
			$(".left_position").width("0%");
			$("#results").show();
			break;
		// browse
		default:
			$(".left_position").width("0%");
			$("#browse_tree").show();
			break;

	}
}
function evaluateBrowseOrResults(){
	// if we're on browse mode either we show nav tree or thumb results if there are any
	if(sSearchMode == "browse"){
		$(".left_position").width("0%");
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
		$("#thumb_results").show();

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
	if(sSearchMode != sMode)
	{
		sSearchMode = sMode;
		evaluateBrowseOrResults();
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