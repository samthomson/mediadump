<?php

class SearchController extends BaseController {

	
	public static function tree()
	{
		// return list of unique folder with live files
		$soFiles = DB::table("files")
		->join("tags", function($join)
			{
				$join->on("files.id", "=", "tags.file_id")
				->where("type", "=", "uniquedirectorypath");
			})		
		->where("indexed", "=", true)->distinct("value")/**/
		->orderBy("datetime", "desc")/**/
        ->groupBy('tags.value')/**/
        ->select("files.id", "files.hash", "tags.value")
		->get();


		return Response::json($soFiles);
	}

	public static function suggest()
	{
		$mtStart = microtime(true);
		// get unique tags from cache
		$aoaTags = CacheController::getSearchSuggestions();
		// search them
		$sSearchTerm = strtolower(Input::get("match"));

		$oaReturn = [];
		/*
		foreach ($oaTags as $oTag) {
			if (strpos($oTag->value, $sSearchTerm) !== false) {
				array_push($oaReturn, $oTag);
			}
		}
		*/
		foreach($aoaTags as $key => $value) {
	        if(preg_match('/^'.$sSearchTerm.'(\d+)$/',$key,$m)) {
	        	array_push($oaReturn, $value);
                //echo "Array element ",$str," matched and number = ",$m[1],"\n";
	        }
		}


		$oaReturn["suggestions"] = array_slice($oaReturn, 0, 16);
		$oaReturn["data"] = array(
			"speed" => (microtime(true) - $mtStart)*1000
			);

		// return limited matches
		return Response::json($oaReturn);
	}
	public static function suggestStats()
	{
		$mtStart = microtime(true);
		// get unique tags from cache
		echo ((microtime(true) - $mtStart)*1000)."<br/>";
		$aoaTags = CacheController::getSearchSuggestions();
		// search them
		
		echo count($aoaTags). " size"."<br/>";


		echo ((microtime(true) - $mtStart)*1000)."<br/>";
	}
	public static function dbSuggest()
	{
		$mtStart = microtime(true);
		// search them
		$sSearchTerm = strtolower(Input::get("match"));

		$oaFiles = DB::table("files")
		->join("tags", function($join)
			{
				$join->on("files.id", "=", "tags.file_id");
			})		
		->where("tags.value", "LIKE", $sSearchTerm."%")->distinct("value")
		->orderBy("datetime", "desc")
        ->groupBy('value')
        ->select("files.id", "files.hash", "tags.value")
		->get();

		$oaReturn["suggestions"] = array_slice($oaFiles, 0, 16);
		$oaReturn["data"] = array(
			"speed" => (microtime(true) - $mtStart)*1000
			);

		// return limited matches
		return Response::json($oaReturn);
	}

	public static function elasticSearch()
	{
		//Clockwork::startEvent('elasticSearch', 'elasticSearch');
		try{
			$params = array();
			$params['hosts'] = array (
				'http://localhost:9200'
			);

			//$params['hosts'] = array ('http://178.62.251.180:9200');
			
			$client = new Elasticsearch\Client($params);
			
			$saResults = [];
			$bShuffle = false;
			$iPerPage = 100;
			$iFrom = 0;
			$sSort = "date";

			$iPage = (Input::get("page")) ? Input::get("page") : 1;
			
			$iFrom = ($iPage-1)*$iPerPage;

			$oResults = array("info" => null, "results" => null);
		
			$sQuery = Input::get("query");
			if(Input::has("sort"))
			{
				switch (Input::get("sort")) {
					case 'date':
					case 'conf':
					case 'rand':
						$sSort = Input::get("sort");
						break;
				}
			}

			$saQueries = explode("|", $sQuery);

			$aMustFilter = [];
			$oGeoQuery = null;

    		$ands = [];
			$nots = [];

			foreach ($saQueries as $sQuery) {
				$saQueryParts = explode("=", $sQuery);
				$bDefaultQuery = false;

				if(count($saQueryParts) > 1)
				{
					switch ($saQueryParts[0]) {
						case 'map':
							$bShuffle = true;
							$iPerPage = 50;
							$iaLatLonParts = explode(",", $saQueryParts[1]);
							
							array_push($ands, ["range" => [
								"latitude" => ["gt" => $iaLatLonParts[0], "lt" => $iaLatLonParts[1]]
							]]);
							array_push($ands, ["range" => [
								"longitude" => ["gt" => $iaLatLonParts[2], "lt" => $iaLatLonParts[3]]
							]]);

							$oGeoQuery = 
								[
									"geo_bounding_box" => 
										[
											"pin.location" => 
												[
													"top_left" => [
														"lat" => (float)$iaLatLonParts[1],
														"lon" => (float)$iaLatLonParts[2]
													],
													"bottom_right" => [
														"lat" => (float)$iaLatLonParts[0],
														"lon" => (float)$iaLatLonParts[3]
													]
												]
										]
								];
							break;
						case 'shuffle':
							$bShuffle = true;
							break;
						
						default:
							$sSort = "date";
							break;
					}
				}else{
					$bDefaultQuery = true;
				}

				if($bDefaultQuery){
					$sSort = "conf";
					if($sQuery == '*')
					{
						array_push($ands, array("match_all" => new \stdClass()));
					}else{
						$bNotQuery = false;

						if(strlen($sQuery) > 0){
							if($sQuery[0] === '!'){
								$bNotQuery = true;
							}
						}

						if($bNotQuery)
							array_push($nots, array("term" => array("tags.value" => substr($sQuery,1))));
						else
							array_push($ands, array("term" => array("tags.value" => $sQuery)));
					}
				}
			}

			// shuffle
			if($bShuffle){
				$sSort = "rand";
			}

			switch ($sSort) {
				case 'rand':
					array_push($ands, array("match_all" => new \stdClass()));
					break;
			}

			$filter = [];

            $filter = ["bool" =>[
				"must" => $ands,
				"must_not" => $nots
		    ]];

		    switch ($sSort) {
				case 'date':
					$sSort = "date";
					$searchParams['sort'] = ["longtime:desc"];
					break;
				case 'conf':
					//////$searchParams['sort'] = ["tags.confidence:desc"];
					$searchParams['sort'] = [
						"tags.confidence" => [
							"order" => "desc",
							"mode" => "min",
							"nested_path" => "tags",
							"nested_filter" => [
								"term" => [
									"tags.value" => "tree"
								]
							]
						]
					];
					break;
			}


		    $searchParams['index'] = 'mediadump_index';
			$searchParams['size'] = $iPerPage;
			$searchParams['from'] = $iFrom;


			$searchParams['body']['query']['filtered'] = array(
			    "filter" => $filter
			);

			
			$searchParams['body'] = array(
				'query' => array(
			        'function_score' => array(
			            'functions' => array(
			                array("random_score" => new \stdClass())
			            ),
			            'filter' => $filter
			        )
			    )			    
			);
						
			
			//print_r($searchParams);exit();

			$retDoc = $client->search($searchParams);

			//print_r($retDoc);exit();

			$saStats = [];
			$soFiles = [];
			$aaSpeeds = [];
			$aaQueryResultsCount = [];

			$saQueryResults = [];
			$iMs = $retDoc["took"];
			$iCount = $retDoc["hits"]["total"];
			$iAvailablePages = round(floor(($iCount-1)/$iPerPage))+1;;


			$oaResults = [];

			foreach($retDoc["hits"]["hits"] as $oHit){
				if(false)
					echo $oHit["_source"]["id"].": ".$oHit["_source"]["datetime"]."<br/>";
				$saResults[$oHit["_source"]["id"]] = $oHit["_source"]["hash"];

				array_push($oaResults, [
					"i" => (int)$oHit["_source"]["id"],
					"ha" => $oHit["_source"]["hash"],
					/*
					"type" => (isset($oHit["_source"]["media_type"]) ? $oHit["_source"]["media_type"] : null),
					*/
					"la" => (isset($oHit["_source"]["latitude"]) ? $oHit["_source"]["latitude"] : null),
					"lo" => (isset($oHit["_source"]["longitude"]) ? $oHit["_source"]["longitude"] : null),
					/*
					"a" => (isset($oHit["_source"]["literal_location"]) ? $oHit["_source"]["literal_location"] : null),
					*//*
					"tags" => $oHit["_source"]["tags"],*/
					"w" => (int)$oHit["_source"]["medium_width"],
					"h" => (int)$oHit["_source"]["medium_height"]
					]);
			}

			//
			// render
			//

			$oaInfo = [
				"speed" => $iMs,
				"count" => $retDoc["hits"]["total"],
				"available_pages" => $iAvailablePages,
				"order" => $sSort
			];

			$iMin = (($iPage * $iPerPage) - $iPerPage);
			$iMax = ($iMin + $iPerPage);


			$oaInfo["lower"] = $iMin + 1;
			$oaInfo["upper"] = (($iPage - 1 ) * $iPerPage) + count($retDoc["hits"]["hits"]);


			$oReturn = [
				"info" => $oaInfo,
				"results" => $oaResults
			];

			return Response::json($oReturn);


		}catch(Exception $e){
			echo $e;
		}
		//Clockwork::endEvent('elasticSearch');
	}	
}