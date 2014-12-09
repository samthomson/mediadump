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
		// return results
		$sQueryType = "value"; //default
		if(count($saQueryParts) > 1){
			// if actually set, see what it is
			$sQueryType = $saQueryParts[0];
		}


		switch($sQueryType)
		{
			case "map":
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
				break;
			case "shuffle":
				$soFiles = DB::table("files")
					->join("tags", function($join)
						{
							$join->on("files.id", "=", "tags.file_id");
						})
					->join("geodata", function($joinGeoData)
						{
							$joinGeoData->on("files.id", "=", "geodata.file_id");
						})	
					->where("live", "=", true)->distinct("value")
					->orderBy(DB::Raw('RAND()'))
					->groupBy("id")
			        ->select($saSelectProperties)
					->get();
				break;
			default:
				$soFiles = DB::table("files")
					->join("tags", function($join)
						{
							$join->on("files.id", "=", "tags.file_id")
							->where("value", "=", Input::get("query"));
						})	
					->join("geodata", function($joinGeoData)
					{
						$joinGeoData->on("files.id", "=", "geodata.file_id");
					})	
					->where("live", "=", true)->distinct("value")
					->where("confidence", ">", Helper::iConfidenceThreshold())
					->orderBy("confidence", "desc")
					->orderBy("datetime", "desc")
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

		$saQueries = explode(",", $sQuery);

		$saStats = [];
		$soFiles = [];

		$saQueryResults = [];

		// get results for all queries
		foreach ($saQueries as $sQuery) {
			$saQueryResults[$sQuery] = self::individualQuery($sQuery);
		}
		// aggregate queries
		switch(count($saQueries))
		{
			case 0:
				$soFiles = [];
				break;
			case 1:
				$soFiles = $saQueryResults[$saQueries[0]];
				break;
			default:
				// multiple
				// make an array of files that were contained in all queries' results
				break;
		}
		// return them, with some stats

/*
		{
			// non standard query, check the type
			switch ($saQueries[0]) {
				case 'map':
					$iaLatLonRange = $saQueries = explode(",", $saQueries[1]);
					if(count($iaLatLonRange) === 4)
					{
						$soFiles = DB::table("files")
						->join("tags", function($join)
							{
								//$join->on("files.id", "=", "tags.file_id")->where("value", "=", "*");
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
				        ->select("files.id", "files.hash", "tags.value", "geodata.latitude", "geodata.longitude", "files.medium_width AS width", "files.medium_height AS height", "tags.confidence as confidence")
						->get();
					}
					break;
				case 'shuffle':
					$soFiles = DB::table("files")
					->join("tags", function($join)
						{
							$join->on("files.id", "=", "tags.file_id");
						})
					->join("geodata", function($joinGeoData)
						{
							$joinGeoData->on("files.id", "=", "geodata.file_id");
						})	
					->where("live", "=", true)->distinct("value")
					->orderBy(DB::Raw('RAND()'))
					->groupBy("id")
			        ->select("files.id", "files.hash", "tags.value", "geodata.latitude", "geodata.longitude", "tags.confidence as confidence")
					->get();

					break;
			}
		}else{

			$soFiles = DB::table("files")
			->join("tags", function($join)
				{
					$join->on("files.id", "=", "tags.file_id")
					->where("value", "=", Input::get("query"));
				})	
			->join("geodata", function($joinGeoData)
			{
				$joinGeoData->on("files.id", "=", "geodata.file_id");
			})	
			->where("live", "=", true)->distinct("value")
			->where("confidence", ">", Helper::iConfidenceThreshold())
			->orderBy("confidence", "desc")
			->orderBy("datetime", "desc")
	        ->select("files.id", "files.hash", "tags.value", "geodata.latitude", "geodata.longitude", "files.medium_width AS width", "files.medium_height AS height", "tags.confidence as confidence")
			->get();
		}
*/

		$saStats["speed"] = (microtime(true) - $mtStart)*1000;
		$saStats["count"] = count($soFiles);
		$saStats["available_pages"] = round(floor((count($soFiles)-1)/$iPerPage))+1;


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
}