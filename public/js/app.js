
var mediadumpApp = angular.module('mediadumpApp', ['ngRoute', 'google-maps']).directive('myRepeatDirective', function() {
  return function(scope, element, attrs) {
	    //scope.justifyImages($("#thumb_results")); 
		//scope.$digest();
	};
});

mediadumpApp.config(function($httpProvider, $sceDelegateProvider){
    delete $httpProvider.defaults.headers.common['X-Requested-With'];

    $sceDelegateProvider.resourceUrlWhitelist([
    // Allow same origin resource loads.
    'self',
    // Allow loading from our assets domain.  Notice the difference between * and **.
    'http://mdcdn/**',
    'http://cdn.samt.st/**'
  ]);

});


/*
$scope.justifyImages($("#thumb_results")); 
$scope.$apply();
*/

mediadumpApp.controller('mediadumpCtrl', function ($location, $scope, $route, $routeParams, $http) {

	$scope.query = "";
	$scope.default_query = "";

	$scope.sort_mode = "datetime";
	$scope.sort_direction = "asc";
	$scope.operator = "and";
	$scope.total_files_in_md = 12447;

	$scope.thumb_height = 180;

	$scope.s_media_dump_url = "http://mediadump.samt.st";
	$scope.s_media_dump_url = "";
	//$scope.s_media_dump_url = "http://mediadump.dev";
	
	$scope.s_cdn_url = $scope.s_media_dump_url;
	//$scope.s_cdn_url = "http://mdcdn";
	
	
	$scope.default_queries = [];
	$http.get($scope.s_media_dump_url + '/api/tree/')
	.then(function(res) {
		$scope.default_queries = res.data;
	});
	
	// app stuff
	
	$scope.bQueryBuilderVisible = false;

	// search stuff
	$scope.search_input_mode = "browse";
	$scope.search_mode = "search";
	$scope.setSearchInputMode = function(sMode){
		$scope.search_input_mode = sMode;
		if(sMode === "map"){
			$scope.refreshSearchMap();
			// grid only when search mode is map
			$scope.search_mode = "search";
		}
		if(sMode === 'shuffle'){
			$scope.search_mode = "search";
			$scope.query = "search=shuffle";
		}
		if(sMode === 'browse'){
			$scope.query = "";
		}
		$scope.setNavSize(sMode);
	}
	
	$scope.page = 1;

	$scope.iLightIndex = -1;

	$scope.i_markers_showing = 0;

	$scope.bShowAdvancedSearch = false;
	$scope.bEventsOn = false;

	var s_land = "#c0c0c0";
	var s_media_dump_color = "#e74c3c";
	var s_silver_colour = "#bdc3c7";
	s_land = s_silver_colour;


	$scope.media_dump_map_options = {
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

	var jo_url_vars = $location.search();
	
	
	if(jo_url_vars.search_mode != undefined){
		$scope.search_mode = jo_url_vars.search_mode;
	}
	if(jo_url_vars.query != undefined){
		$scope.query = jo_url_vars.query;
	}
	if(jo_url_vars.page  != undefined){
		$scope.page = parseInt(jo_url_vars.page);
	}
	if(jo_url_vars.file  != undefined){
		$scope.iLightIndex = parseInt(jo_url_vars.file);
	}
	$scope.bEventsOn = true;

	$scope.results = [];
	$scope.available_filters = [];

	$scope.bResults = function(){
		if(typeof $scope.results === 'undefined')
			return false;
		return ($scope.results.length > 0) ? true : false;
	}
	$scope.addFilter = function($index){
		// add filters to search query
		$scope.query += "," + $scope.available_filters[$index].value[0];
	}
	$scope.results_bounds = {
		northeast: {
			latitude:0,
			longitude:0
		},
		southwest: {
			latitude:0,
			longitude:0
		}
	};

	$scope.search_info = [];
	$scope.result_info = [];
	$scope.bSearching = false;


	// ui stuff
	$scope.sLightboxURL = "";
	$scope.sLightboxPlace = "";
	$scope.map_settings = {"centre": [0, 0], "zoom": 1};

	//
	// data interfaces
	//
	$scope.search_map = {
	    center: {
	        latitude: 0,
	        longitude: 0
	    },
	    bounds: {},
	    zoom: 1,
	    events: {
		    idle: function (map) {
		    	$scope.$apply(function () {
		    		if(typeof $scope.search_map !== 'undefined'){

			    		var llBounds = map.getBounds();

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

				    	$scope.query = sQuery;  
				    }
		    		
				});
		    }
	    }
	};	
	$scope.markers = [];
	$scope.map = {
	    center: {
	        latitude: 45,
	        longitude: -73
	    },
	    zoom: 8,
	    bounds: {}
	};	

	$scope.sourceFromData = function(sBase){
		return 'data:image/jpeg;base64, '+sBase;
	}
	$scope.setNavSize = function(sMode){
		// set size of left nav section and results to give best UX for current search/browse mode
		var iLeftWidth = "0%";
		switch(sMode){
			case "map":
				iLeftWidth = "45%";
				break;
			case "browse":
				//iLeftWidth = "50%";
				iLeftWidth = "0%";
				break;
			case "search":
				//iLeftWidth = "50%";
				iLeftWidth = "0%";
				break;
			case "shuffle":
				//iLeftWidth = "50%";
				iLeftWidth = "0%";
				break;
		}
		/**/
		$(".left_position").width(iLeftWidth);
		$(".right_position").css("left", iLeftWidth);
		
		/*
		$(".left_position").animate({'width':iLeftWidth},700);
		$(".right_position").animate({'left':iLeftWidth},700);
		*/
	}

	$scope.bMapVisible = function(){
		if ($scope.search_mode === 'map')
			if (!$scope.bSearching)
				return true;
		/*
			if(!$scope.bSearching){
				google.maps.event.trigger(map, 'resize');
				return true;
			}
*/
		return false;
	};


	//
	// 'events'
	//
	$scope.$watch('query', function(){
		if($scope.bEventsOn)
			$scope.page = 1;
		$scope.do_search();
		$scope.reconstruct_url();
	});
	$scope.$watch('page', function(){
		if($scope.page < 1 && $scope.bEventsOn){
			$scope.page = 1;
		}
		$scope.do_search();
		$scope.reconstruct_url();
	});
	$scope.$watch('sort_mode', function(){
		$scope.do_search();
		$scope.reconstruct_url();
	});
	$scope.$watch('operator', function(){
		$scope.do_search();
		$scope.reconstruct_url();
	});
	$scope.$watch('sort_direction', function(){
		$scope.do_search();
		$scope.reconstruct_url();
	});
	$scope.$watch('iLightIndex', function(){
		
		$scope.reconstruct_url();	
		$scope.stop_videos();	
		if($scope.iLightIndex > -1){

			if($scope.results.length === 0){
				//console.log("results not ready yet, despite index");
			}else{
				$scope.preload_around();
				$scope.showVideoInLightbox();
			}
		}
	});
	$scope.$watch('search_mode', function(){
		$scope.do_search();
		$scope.reconstruct_url();
	});
	$scope.$watch('search_input_mode', function(){
		$scope.setNavSize($scope.search_input_mode);
	});



	$scope.$watch('results', function(){
		// reset markers structure that we'll return (so that it can be returned empty if no resutls)
		$scope.markers = [];

		var mTempMarker = {};

		// go through all results and parse them into what a marker requires
		$scope.i_markers_showing = 0;
		for(var cResult = 0; cResult < $scope.results.length; cResult++){

			// only make marker if geo tag exists
			if(typeof $scope.results[cResult].latitude !== "undefined" &&
				typeof $scope.results[cResult].longitude !== "undefined" &&
				$scope.results[cResult].latitude !== 0 &&
				$scope.results[cResult].longitude !== 0)
			{
				var pinIcon = new google.maps.MarkerImage(
					$scope.urlFromHash('icon', $scope.results[cResult], ''),
				    null, /* size is determined at runtime */
				    null, /* origin is 0,0 */
				    null, /* anchor is bottom center of the scaled image */
				    new google.maps.Size(32, 32)
				);  

				mTempMarker = {
		            latitude: $scope.results[cResult].latitude,
		            longitude: $scope.results[cResult].longitude,
		            title: 'm' + cResult,
		            icon: pinIcon
		        };
		        mTempMarker["id"] = cResult;
		        $scope.markers.push(mTempMarker);
		        $scope.i_markers_showing++;
		    }
		}

		if($scope.iLightIndex > -1 && $scope.iLightIndex < $scope.results.length){
			if($scope.results[$scope.iLightIndex].type === "video"){
				$scope.showVideoInLightbox();
			}
		}

		
	});

	$scope.justifyImages = function(uniqueDiv){

		var iRightMargin = 4;
		var allImages = $('img', uniqueDiv);

		var iAvailableWidth = Math.ceil($(uniqueDiv).innerWidth()) - 0;
		
		var sGalleryHTMLBuilder = '';

		var iRunningRowWidth = 0;
		var aRowImageIds = [];


		$scope.results.forEach(function(result, index){
		    // add image to row
		    aRowImageIds.push(index);

		    var img = new Image();
		    img.src = $scope.urlFromHash("medium", result);
		    /*
		    if($scope.search_mode === "browse"){
		    	img.src = $scope.urlFromHash("medium", result);
		    }else{
		    	img.src = $scope.urlFromHash("small", result);
		    }
			*/

		    // add up height
		    iRunningRowWidth += (img.width + iRightMargin);
		    // check row size
		    if(iRunningRowWidth > iAvailableWidth){
		        // calculate resize index
		        var iHeight = img.height;
		        console.log("image height: "+ iHeight);
		        var iRowHeight = Math.floor(iHeight*(iAvailableWidth / iRunningRowWidth));

		        console.log("available width: " + iAvailableWidth);
		        console.log("row width: " + (iRunningRowWidth*(iAvailableWidth / iRunningRowWidth)));

		        
		        // finish row, start next, resize row
		        for(var cImage = 0; cImage < aRowImageIds.length; cImage++){
		        	$scope.results[aRowImageIds[cImage]].height = iRowHeight;
		        }
		        // reset row
		        iRunningRowWidth = 0;
		        aRowImageIds = [];
		    }else{
		        // nothing, carry on to add next image
		    }
		});
	}


	$scope.markersEvents = {
        click: function (gMarker, eventName, model) {            
        	$scope.map_icon_click(model.id);
        }
    };
    $scope.bMapRefreshed = false;

    $scope.refreshSearchMap = function(){
    	$scope.bMapRefreshed = false;
    	$scope.bMapRefreshed = true;
    }
    $scope.setOperator = function(sOperator){
    	$scope.operator = sOperator;
    	$scope.bShowUpdateButton = true;
    }

	$scope.$watch('result_info', function(){
		var oTempFilter = {};
		$scope.available_filters = [];
		/*
		if(typeof $scope.result_info.distinct !== 'undefined')
			for(var cFilter = 0; cFilter < $scope.result_info.distinct.length; cFilter++){
				oTempFilter = {
					"value": $scope.result_info.distinct[cFilter],
					"add": false
				};
				$scope.available_filters.push(oTempFilter);
			}
		*/
	});

	
	$scope.boundsChanged = function(){
		console.log("var");
	};

	$scope.new_bounds = function(){
		return $scope.results_bounds;
	};


	$scope.reset = function(){
		$scope.query = "*";
		$scope.search_mode = "search";
	};

	// ui logic
	$scope.b_results = function() {
		if($scope.results.length > 0 || $scope.search_mode !== 'search')
			return true;
		else
			return false;
	};
	$scope.b_query = function() {
		if($scope.query == '')
			return false;
		else
			return true;
	};
	$scope.do_default_query = function(s_new_query) {
		$scope.query = s_new_query;
	};
	$scope.lightChange = function(iDelta){
		if($scope.iLightIndex + iDelta === -1){
			// go to last
			$scope.iLightIndex = $scope.results.length - 1;
		}
		else if($scope.iLightIndex + iDelta === $scope.results.length){
			// go to first
			$scope.iLightIndex = 0;
		}else{
			$scope.iLightIndex += iDelta;
		}
	};
	/*
	$scope.lightRight = function(){		
		if($scope.iLightIndex < $scope.results.length -1){
			$scope.iLightIndex++;
			$scope.sLightboxURL = $scope.urlFromHash('lightbox', $scope.results[$scope.iLightIndex].h, $scope.results[$scope.iLightIndex].e);
			$scope.sLightboxPlace = $scope.results[$scope.iLightIndex].p;
		}else{
			$scope.iLightIndex = 0;
		}
	};
	*/

	$scope.folderFromUniqueDir = function(sDir){
		var sa = sDir.split("/");
		var iIndex = sa.length - 1;
		if(iIndex < 0)
			iIndex = 0;

		if(sa.length> 0)
			return sa[iIndex];
		else
			return "";
	}

	$scope.urlFromHash = function(sMode, oObject, sExt){
		if(typeof oObject === "undefined")
			return "";
		switch(sMode){
			case 'lightbox':
				return $scope.s_cdn_url + '/thumbs/large/'+oObject.hash+'.jpg';
				break;
			case 'icon':
				return $scope.s_cdn_url + '/thumbs/icon/'+oObject.hash+'.jpg';
				break;
			case 'thumbs':
				return $scope.s_cdn_url + '/thumb/'+oObject.id+'.jpg';
			case 'medium':
				return $scope.s_cdn_url + '/thumbs/medium/'+oObject.hash+'.jpg';
				break;
			case 'results':
				var sThumbSize = ($scope.search_input_mode === "browse") ? "medium" : "small";
				return $scope.s_cdn_url + '/thumbs/' + sThumbSize + '/'+oObject.hash+'.jpg';
				break;
		}
	};
	$scope.videoSRC = function(oObject, sType){
		var sSource = "";
		if(typeof oObject === "undefined"){
			sSource = "";
			console.log("undefined object to make video url from");
		}else{
			switch(sType){
				case "webm":
					sSource = $scope.s_cdn_url + "/video/" + oObject.id + ".webm";
					break;
				case "ogv":
					sSource = $scope.s_cdn_url + "/video/" + oObject.id + ".ogv";
					break;
				case "mp4":
					sSource = $scope.s_cdn_url + "/video/" + oObject.id + ".mp4";
					break;
			}
		}
		return sSource;
	}

	$scope.videoSRCs = function(oObject){
		if(typeof oObject === "undefined"){
			sSource = "";
			console.log("undefined object to make video url from");
		}else{
			var sSrcBase = $scope.s_cdn_url + "/video/";
			return [
				{src: sSrcBase + oObject.id + '.mp4', type: "video/mp4"},
				{src: sSrcBase + oObject.id + '.webm', type: "video/webm"},
				{src: sSrcBase + oObject.id + '.ogv', type: "video/ogg"}
			];
		}
	}
	$scope.lightboxURL = function(){
		if(typeof $scope.results[$scope.iLightIndex] !== "undefined"){
			//if($scope.results[$scope.iLightIndex].type === "image"){
				return $scope.s_cdn_url + "/thumbs/large/" + $scope.results[$scope.iLightIndex].hash + ".jpg";
			//}
		}else{return "undefined item";}
	}

	$scope.preload_thumb = function(index){
		if(typeof $scope.results[index] !== "undefined")
		{
			switch($scope.results[index].type){
				case "image":				
					$scope.preloadImage($scope.urlFromHash('lightbox', $scope.results[index], 'jpg'));
					break;
				default:
					// do nothing
					break;
			}
		}
		else
			console.log("can't preload undefined element");
	}

	$scope.preloadImage = function(sURL){
		try {
			var _img = new Image();
            _img.src = sURL;
        } catch (e) { }		
	}

	$scope.preload_around = function(){
		if($scope.iLightIndex > -1 && $scope.results.length > 0){
			var saPreloadURLS = [];

			for(cImage = $scope.iLightIndex - 2, cPreloadCount = 0; cPreloadCount < 5; cImage++, cPreloadCount++){
				if(cImage > -1 && cImage < $scope.results.length){
					if($scope.results[cImage].type === "image")
						saPreloadURLS.push($scope.urlFromHash('lightbox', $scope.results[cImage], 'jpg'));
				}
			}			
			
			saPreloadURLS.forEach(function(value){
				$scope.preloadImage(value);
			});
		}
	}

	$scope.reconstruct_url = function(){
		if($scope.query !== ''){
			$location.search('query', $scope.query);
			$location.search('page', $scope.page);

			if($scope.iLightIndex > -1){
				$location.search('file', $scope.iLightIndex);
			}else{
				$location.search('file', null);
			}
		}else{
			$location.search('query', null);
			$location.search('page', null);
			$location.search('file', null);
		}

		if($scope.search_mode != 'search'){
			$location.search('search_mode', $scope.search_mode);
		}else{
			$location.search('search_mode', null);
		}


		if($scope.sort_mode != 'datetime'){
			$location.search('sort_mode', $scope.sort_mode);
		}else{
			$location.search('sort_mode', null);
		}



		if($scope.sort_direction != 'asc'){
			$location.search('sort_direction', $scope.sort_direction);
		}else{
			$location.search('sort_direction', null);
		}

		if($scope.operator != 'and'){
			$location.search('operator', $scope.operator);
		}else{
			$location.search('operator', null);
		}
	};

	$scope.showVideoInLightbox = function(){
		if($scope.results[$scope.iLightIndex].type === "video"){
			// initiate flowplayer
			var sSrcBase = "http://mdcdn/thumb/video/";
			var oObject = $scope.results[$scope.iLightIndex];
			var s_ogv = sSrcBase + oObject.id + '.ogv';
			var s_mp4 = sSrcBase + oObject.id + '.mp4';
			var s_webm = sSrcBase + oObject.id + '.webm';
								
			var html_lightbox = '<div class="f-p" data-swf="/flowplayer.swf"><video autoplay><source src="' + s_webm +'" type="video/webm"/><source src="' + s_mp4 + '" type="video/mp4"/><source src="' + s_ogv + '" type="video/ogv"/></video></div>';				
			html_lightbox += '<script>var api = $(".f-p").flowplayer();</script>';
			
			$("#player").html(html_lightbox);
		}
	}

	$scope.do_search = function() {
		$scope.results = [];
		$scope.search_info = [];

		if($scope.query !== ""){
			$scope.bSearching = true;	
			$scope.bShowAdvancedSearch = false;
			$http({
		        method  : 'GET',
		        /*url     : 'http://media-dump-instant/api/search',
		        url     : 'http://media-dump.samt.st/api/search',*/
		        url     : $scope.s_media_dump_url + '/api/search/',
		        params    : {query: $scope.query, page: $scope.page, m: $scope.search_mode, operator: $scope.operator, sort: $scope.sort_mode, sort_direction: $scope.sort_direction, "search_input": $scope.search_input_mode}
		    })
	        .success(function(data) {
	            if(data != undefined){
	            	$scope.results = data.results;
	            	$scope.justifyImages($("#thumb_results"));
		            
		            $scope.result_info = data.info;
				}else{
	            	// if not successful, bind errors to error variables
					$scope.results = [];
					$scope.search_info = [];					
				}
				$scope.bSearching = false;
	        })
	        .error(function() {
        		console.log("http search error :(");
				$scope.bSearching = false;
				$scope.results = [];
				$scope.search_info = [];
	        });
        }
	};

	$scope.thumb_click = function(index) {
		$scope.iLightIndex = index;
	}

	$scope.stop_videos = function(){
		$(".f-p").remove();
	}
	$scope.closeLightbox = function(){
		$scope.stop_videos();
		$scope.iLightIndex = -1;
	}





	$scope.map_icon_click = function(index) {
		$scope.thumb_click(index);
		$scope.$apply();
	}

	/*
	$scope.map_icon_click = function(event, index) {
		console.log("icon clicked: " + index)
		$scope.iLightIndex = index;
		$scope.$apply();
	}*/
	$scope.map_changed = function(event) {
		// only fire this event if we're actually doing a geo search
		if($scope.search_mode == 'map' || $scope.search_mode == 'search_map'){
			// the map has been interacted with, so we'll make a geo search for the user
			// for now we replace their search with this geo-search, later we'll re-support multiquery searches
			var llBounds = this.getBounds();
			var fNELat = llBounds.getNorthEast().lat();
			var fNELon = llBounds.getNorthEast().lng();
			var fSWLat = llBounds.getSouthWest().lat();
			var fSWLon = llBounds.getSouthWest().lng();

			var sGeoQuery = "map=" + fSWLat + "," + fNELat + "," + fSWLon + "," + fNELon;

			console.log(sGeoQuery);
			$scope.query = sGeoQuery;
			$scope.page = 1;
			$scope.$apply();
		}
	}

	$scope.pagination_enabled = function(b_previous){
		if(b_previous){
			if($scope.page == 1)
				return false;
			else
				return true;
		}else{
			if($scope.page == $scope.search_info.available_pages)
				return false;
			else
				return true;
		}
	}
	$scope.show_pagination = function(){
		if($scope.search_mode === 'map'){
			return false;
		}else{
			return ($scope.search_info.available_pages > 1);
		}
	}
	$scope.show_map_summary = function(){
		if($scope.search_mode === 'map'){
			return true;
		}else{
			return false;
		}
	}
	$scope.map = {
	    center: {
	        latitude: 0,
	        longitude: 0
	    },
	    zoom: 1
	};

	$scope.status = function(){
		var s_message = "";


		return s_message;
	}

	//
	// hooked in jquery events
	//
	$('body').keydown(function (e) {
		var iAcceptedCodes = [37, 39];

		if(iAcceptedCodes.indexOf(e.keyCode) > -1){
		    $scope.$apply(function () {
		    	switch(e.keyCode){
		    		case 37:
		    			// left
		        		$scope.lightChange(-1);
		        		break;
		    		case 39:
		    			// left
		        		$scope.lightChange(1);
		        		break;
		    	}
		    })
		}
	});

	var loop;

	/*
	$(window).resize(function() {
		clearTimeout(loop);
		loop = setTimeout(doneResizing, 500);	
	});
	function doneResizing(){
		//$scope.justifyImages($("#thumb_results")); 
		$scope.$apply();
	}
	*/
});
