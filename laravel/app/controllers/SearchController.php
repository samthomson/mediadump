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
		$sQuery = Input::get("query");

		if($sQuery !== ""){
			$soFiles = FileModel::whereHas("tags", function($q)
				{
					$q->where("value", "=", Input::get("query"));
				})->get();

			if(!Input::get("render"))
				return Response::json($soFiles);
			else
				foreach ($soFiles as $value) {
					echo '<img src="/thumbs/medium/'.$value->id.'.jpg"/>';
				}
		}
	}
}
