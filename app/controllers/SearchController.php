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

	public static function search()
	{		
		$mtStart = microtime(true);

		$iPerPage = 5;

		$oResults = array("info" => null, "results" => null);
		
		$sQuery = Input::get("query");

		$saQueries = explode("=", $sQuery);

		$saStats = [];
		$soFiles = [];

		if(count($saQueries) > 1)
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
				        ->select("files.id", "files.hash", "tags.value", "geodata.latitude", "geodata.longitude")
	        ->take(100)
						->get();
					}
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
			->orderBy("datetime", "desc")
	        ->select("files.id", "files.hash", "tags.value", "geodata.latitude", "geodata.longitude")
			->get();
		}
		$saStats["speed"] = (microtime(true) - $mtStart)*1000;
		$saStats["count"] = count($soFiles);
		$saStats["available_pages"] = round((floor(count($soFiles)-1)/$iPerPage))+1;


		$iPage = (Input::get("page")) ? Input::get("page") : 1;

		$iMin = (($iPage * $iPerPage) - $iPerPage);
		$iMax = ($iMin + $iPerPage);

		$oResults["results"] = array_slice($soFiles, $iMin, $iPerPage);
		$oResults["info"] = $saStats;

		return Response::json($oResults);
		/*
		if($sQuery !== ""){
			$soFiles = FileModel::whereHas("tags", function($q)
				{
					$q->where("value", "=", Input::get("query"));
				})
			->whereHas("geodata", function($g)
			{
				/*$g->where("id", "=", "geodate.file_id");
			})
			->where("live", "=", true)
			->orderBy("datetime", "desc")
			->get();

			


			if(!Input::get("render"))
				
			else
				foreach ($soFiles as $value) {
					echo '<img src="/thumbs/medium/'.$value->id.'.jpg"/>';
				}
		}*/
		
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
        ->select("files.id", "tags.value")
		->get();


		return Response::json($soFiles);
	}
}
