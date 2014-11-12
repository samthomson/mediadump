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
		$oResults = array("info" => null, "results" => null);
		
		$sQuery = Input::get("query");

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
        ->select("files.id", "tags.value", "geodata.latitude", "geodata.longitude")
		->get();


		$oResults["results"] = $soFiles;
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
        ->select("files.id", "tags.value", "geodata.latitude", "geodata.longitude")
		->get();


		return Response::json($soFiles);
	}
}
