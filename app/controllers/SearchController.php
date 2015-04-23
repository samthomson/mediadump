<?php

class SearchController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/
	private static function individualQuery($sQuery){
		// construct db query based on broken down query (type)
		$saQueryParts = explode("=", $sQuery);
		$soFiles = [];
		$saSelectProperties = array("files.id", "files.hash", "tags.value", "geodata.latitude", "geodata.longitude", "files.medium_width AS width", "files.medium_height AS height", "tags.confidence as confidence");
		$saSelectPropertiesWithoutTags = array("files.id", "files.hash", "geodata.latitude", "geodata.longitude", "files.medium_width AS width", "files.medium_height AS height");
		// return results
		$sQueryType = "value"; //default
		if(count($saQueryParts) > 1){
			// if actually set, see what it is
			$sQueryType = $saQueryParts[0];
		}


		switch($sQueryType)
		{
			case "map":
				$iaLatLonRange = explode(',', $saQueryParts[1]);


				$soFiles = DB::table("files")
					->join("geodata", function($joinGeoData) use ($iaLatLonRange)
					{
						$joinGeoData->on("files.id", "=", "geodata.file_id")
						
						->where("latitude", ">", $iaLatLonRange[0])
						->where("latitude", "<", $iaLatLonRange[1])
						->where("longitude", ">", $iaLatLonRange[2])
						->where("longitude", "<", $iaLatLonRange[3]);
					})	
					->where("live", "=", true)->distinct("value")
					->orderBy(DB::Raw('RAND()'))
					->groupBy("id")
			        ->select($saSelectPropertiesWithoutTags)
					->get();

					$queries = DB::getQueryLog();
					$last_query = end($queries);

				break;
			case "shuffle":
				$soFiles = DB::table("files")/*
					->join("tags", function($join)
						{
							$join->on("files.id", "=", "tags.file_id");
						})*/
					->join("geodata", function($joinGeoData)
						{
							$joinGeoData->on("files.id", "=", "geodata.file_id");
						})	
					->where("live", "=", true)->distinct("value")
					->orderBy(DB::Raw('RAND()'))
					->groupBy("id")
			        ->select($saSelectPropertiesWithoutTags)
					->get();
				break;
			default:
				$soFiles = DB::table("files")
					->join("tags", function($join) use ($sQuery)
						{
							$join->on("files.id", "=", "tags.file_id")
							->where("value", "=", $sQuery);
						})	
					->join("geodata", function($joinGeoData)
					{
						$joinGeoData->on("files.id", "=", "geodata.file_id");
					})	
					->where("live", "=", true)->distinct("value")
					->where("confidence", ">", Helper::iConfidenceThreshold())
					->orderBy("confidence", "desc")
					->orderBy("datetime", "desc")
					->groupBy("id")
			        ->select($saSelectProperties)
					->get();
				break;
		}

		return $soFiles;
	}

	public static function sqlSearch()
	{		
		$mtStart = microtime(true);

		$iPerPage = 100;

		$oResults = array("info" => null, "results" => null);
		
		$sQuery = Input::get("query");

		$saQueries = explode("|", $sQuery);

		$saStats = [];
		$soFiles = [];
		$aaSpeeds = [];
		$aaQueryResultsCount = [];

		$saQueryResults = [];

		// get results for all queries
		$i = 0;
		foreach ($saQueries as $sQuery) {
			$saQueryResults[$i] = self::individualQuery($sQuery);
			$aaQueryResultsCount[$sQuery] = count($saQueryResults[$i]);
			$i++;
		}
		$aaSpeeds["searched"] = Helper::iMillisecondsSince($mtStart);
		// aggregate queries
		switch(count($saQueries))
		{
			case 0:
				$soFiles = [];
				break;
			case 1:
				$soFiles = $saQueryResults[0];
				break;
			default:
				// multiple
				// make an array of files that were contained in all queries' results

				// start with the shortest
            	usort($saQueryResults, create_function('$a, $b', 'return bccomp(count($a), count($b));'));

            	
				// merge results on intersecting


                if($saQueryResults[0] == null){$soFiles = array();}
            
                for($cArr = 1; $cArr < count($saQueryResults); $cArr++){
                    if($saQueryResults[$cArr] == null){$soFiles = array();}
                    
                	// merge two results on intersecting
                    $aIntersecting = [];
                    $index = [];

                    if(isset($saQueryResults[0])){
                        foreach ($saQueryResults[0] as $item) {
                            $index[$item->hash] = true;
                        }
                    }
                    if(isset($saQueryResults[$cArr])){
                    	foreach ($saQueryResults[$cArr] as $item) {
                            if (isset($index[$item->hash])) {
                            	array_push($aIntersecting, $item);
                            }
                        }
                    }
                    $saQueryResults[0] = $aIntersecting;
                    
                    //unset($aIntersecting);
                    //unset($index);            
                }
                $soFiles = $saQueryResults[0];

				break;
		}
		$aaSpeeds["aggregated"] = Helper::iMillisecondsSince($mtStart);
		// return them, with some stats


		$saStats["speed"] = Helper::iMillisecondsSince($mtStart);
		$saStats["speed_breakdown"] = $aaSpeeds;
		
		$saStats["count"] = count($soFiles);
		$saStats["available_pages"] = round(floor((count($soFiles)-1)/$iPerPage))+1;

		$saStats["queries"] = [];


		foreach($aaQueryResultsCount as $key => $value){
			$saStats["queries"][$key] = $value;
		}

		$iPage = (Input::get("page")) ? Input::get("page") : 1;

		$iMin = (($iPage * $iPerPage) - $iPerPage);
		$iMax = ($iMin + $iPerPage);


		$oResults["results"] = array_slice($soFiles, $iMin, $iPerPage);

		$saStats["lower"] = $iMin + 1;
		$saStats["upper"] = (($iPage - 1 ) * $iPerPage) + count($oResults["results"]);

		$oResults["info"] = $saStats;

		return Response::json($oResults);		
	}

	public static function tree()
	{
		// return list of unique folder with live files
		$soFiles = DB::table("files")
		->join("tags", function($join)
			{
				$join->on("files.id", "=", "tags.file_id")
				->where("type", "=", "uniquedirectorypath");
			})		
		->where("live", "=", true)->distinct("value")
		->orderBy("datetime", "desc")
        ->groupBy('value')
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
		try{
			$client = new Elasticsearch\Client();

			$saResults = [];
			$bShuffle = false;
			$iPerPage = 100;
			$iFrom = 0;

			$iPage = (Input::get("page")) ? Input::get("page") : 1;
			
			$iFrom = ($iPage-1)*$iPerPage;

			$searchParams['index'] = 'mediadump_index';
			$searchParams['size'] = $iPerPage;
			$searchParams['from'] = $iFrom;

			
			$oResults = array("info" => null, "results" => null);
		
			$sQuery = Input::get("query");

			$saQueries = explode("|", $sQuery);

			$oaQueries = [];

			foreach ($saQueries as $sQuery) {
				$saQueryParts = explode("=", $sQuery);
				$bDefaultQuery = false;

				if(count($saQueryParts) > 1)
				{
					switch ($saQueryParts[0]) {
						case 'map':
							$bShuffle = true;
							$iaLatLonParts = explode(",", $saQueryParts[1]);
							
							array_push($oaQueries, array('range' => array('latitude' => array('gt' => $iaLatLonParts[0],'lt' => $iaLatLonParts[1]))));
							array_push($oaQueries, array('range' => array('longitude' => array('gt' => $iaLatLonParts[2],'lt' => $iaLatLonParts[3]))));
							break;
						case 'shuffle':
							$bShuffle = true;
							break;
						
						default:
							$bDefaultQuery = true;
							break;
					}
				}else{
					$bDefaultQuery = true;
				}

				if($bDefaultQuery){
					array_push($oaQueries, array('query_string' => array("default_field" => 'tags.value', "query" => '"'.$sQuery.'"')));
				}
			}

			// shuffle
			if(!$bShuffle){
				$searchParams['sort'] = array("longtime:desc", "ignore_unmapped:true");
			}

			$searchParams['body'] = array(
			    'query' => array(
			        'function_score' => array(
			            'functions' => array(
			                array("random_score" => new \stdClass())
			            ),
			            'query' => array('bool' => array("must" => $oaQueries))
			        )
			    )
			);
			print_r(json_encode($searchParams['body']['query']['function_score']['query']));
			exit();
			
			$retDoc = $client->search($searchParams);

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
				//print_r($oHit);
				
				if(false)
					echo $oHit["_source"]["id"].": ".$oHit["_source"]["datetime"]."<br/>";
				/*echo "<br/><br/>";*/

				$saResults[$oHit["_source"]["id"]] = $oHit["_source"]["hash"];

				//array_push($saResults, $oHit["_source"]["hash"]);
				array_push($oaResults, [
					"id" => $oHit["_source"]["id"],
					"hash" => $oHit["_source"]["hash"],
					"latitude" => $oHit["_source"]["latitude"],
					"longitude" => $oHit["_source"]["longitude"],/*
					"tags" => $oHit["_source"]["tags"],*/
					"width" => $oHit["_source"]["medium_width"],
					"height" => $oHit["_source"]["medium_height"]
					]);
			}

			//
			// redner
			//

			$oaInfo = [
				"speed" => $iMs,
				"count" => $retDoc["hits"]["total"],
				"available_pages" => $iAvailablePages
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
	}	
}