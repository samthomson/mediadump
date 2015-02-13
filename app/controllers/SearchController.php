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


				// old way, super slow on large queries (whole world map)
				/*
				$soFiles = DB::table("files")
					->join("tags", function($join)
						{
							$join->on("files.id", "=", "tags.file_id");
						})	
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
			        ->select($saSelectProperties)
					->get();

				*/
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

	public static function search()
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

	// elastic search tests
	public static function testIndex()
	{
		try{
			$mtStart = microtime(true);



			// get clients
			$client = new Elasticsearch\Client();

			/*
			delete pre-existing
			$deleteParams['index'] = 'test_index';
			$client->indices()->delete($deleteParams);
			*/


			// set up index
			/*
			$indexParams['index']  = 'test_index';

			$myTypeMapping = array(
			    '_source' => array(
			        'enabled' => true
			    ),
			    'properties' => array(
			        'datetime' => {
			        	"type" : "date",
			        	"format" : "yyyy-MM-dd HH:mm:ss"}
			        )
			    )
			);

			$indexParams['body']['mappings']['my_type'] = $myTypeMapping;
			
			// Create the index
			$client->indices()->create($indexParams);
			*/

			$iLimit = 400;

			// select objects to index
			/*$oaFiles = DB::table("files")
			->join("tags", "files.id", "=", "tags.file_id")
			->join("geodata", "files.id", "=", "geodata.file_id")
			->take($iLimit)->get();*/

			$oaFiles = FileModel::take($iLimit)->where("live", "=", 1)->get();

			foreach($oaFiles as $oFile){

				$aaTags = [];
				$saTags = [];

				foreach($oFile->tags as $oTag){

					if($oTag->type === "uniquedirectorypath")
						echo "value: ".$oTag->value."<br/>";
					array_push($aaTags, array(
						"type" => $oTag->type,
						"value" => $oTag->value,
						"confidence" => $oTag->confidence));
				}


				
				$params = array();
				$params["body"] = array(
					"hash" => $oFile->hash,
					"id" => $oFile->id,
					"tags" => $aaTags,
					"latitude" => $oFile->geoData->latitude,
					"longitude" => $oFile->geoData->longitude,
					"datetime" => $oFile->datetime,
					"longtime" => strtotime($oFile->datetime)
				);
				$params["index"] = "test_index";
				$params["type"] = "my_type";
				$params["id"] = $oFile->id;

				$ret = $client->index($params);

			}			

			$iFiles = count($oaFiles);
			$iTime = Helper::iMillisecondsSince($mtStart);
			$fPerFile = $iTime / $iFiles;
			echo "select all files ($iFiles/$iLimit) @ $iTime ms, av $fPerFile per file";

		}catch(Exception $e){
			echo $e;
		}
	}

	public static function testSearch()
	{
		try{
			$client = new Elasticsearch\Client();

			$saResults = [];


			$searchParams['index'] = 'test_index';
			$searchParams['size'] = 100;


			/*
			if(Input::get("q") !== null){
				//$searchParams['body']['query']['match']['tags.value'] = Input::get("q");
				$searchParams['body']['query']['match']['tags.value'] = Input::get("q");
			}
			$searchParams['sort'] = array("longtime:desc");
			*/
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
							$iaLatLonParts = explode(",", $saQueryParts[1]);
							//print_r($iaLatLonParts);
							/*
							$searchParams['body']['query']['range']['latitude'] = array('gt' => $iaLatLonParts[0],'lt' => $iaLatLonParts[1]);
							$searchParams['body']['query']['range']['longitude'] = array('gt' => $iaLatLonParts[2],'lt' => $iaLatLonParts[3]);
							*/
							array_push($oaQueries, array('range' => array('latitude' => array('gt' => $iaLatLonParts[0],'lt' => $iaLatLonParts[1]))));
							array_push($oaQueries, array('range' => array('longitude' => array('gt' => $iaLatLonParts[2],'lt' => $iaLatLonParts[3]))));
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
			$searchParams['body']['query']['bool']['must'] = $oaQueries;

			//print_r($oaQueries);

			$saStats = [];
			$soFiles = [];
			$aaSpeeds = [];
			$aaQueryResultsCount = [];

			$saQueryResults = [];



			$retDoc = $client->search($searchParams);

			$iMs = $retDoc["took"];
			$iCount = count($retDoc["hits"]["hits"]);


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
					"longitude" => $oHit["_source"]["longitude"],/**/
					"tags" => $oHit["_source"]["tags"],
					"width" => 450,
					"height" => 300
					]);
			}

			//
			// redner
			//
			//print_r($retDoc);

			$oaInfo = [
				"speed" => $iMs,
				"count" => $retDoc["hits"]["total"],
				"lower" => 1,
				"upper" => 100
			];


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