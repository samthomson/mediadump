<?php

class CacheController extends BaseController {


	public static function loadSearchTags()
	{
		// create an array of objects to be used in drop down search filter

	}
	public static function getSearchSuggestions($sTerm)
	{
		$soFiles = DB::table("files")
		->join("tags", function($join)
			{
				$join->on("files.id", "=", "tags.file_id");
			})	
		->where("tags.value", "LIKE", "%$sTerm%")->distinct("value")
		->orderBy("tags.confidence")
		->groupBy("tags.value")
        ->select("files.id", "files.hash", "tags.value AS text")
		->take(10)
		->get();

		return $soFiles;
	}
}
