<?php

class CacheController extends BaseController {

	private static $iDefaultCachePeriod = 60;


	public static function loadSearchTags()
	{
		// create an array of objects to be used in drop down search filter

	}
	public static function getSearchSuggestions()
	{
		$sKey = "searchsuggestions";
		if (Cache::has($sKey))
		{
			return Cache::get($sKey);
		}else{
			$oaObjectForCache = self::generateSearchSuggestions();

			if(isset($oaObjectForCache))
				Cache::forever($sKey, $oaObjectForCache);

			return $oaObjectForCache;
		}			
	}

	public static function rebuild(){
		Cache::forever("searchsuggestions", self::generateSearchSuggestions());
	}

	private static function generateSearchSuggestions(){
		return DB::table("files")
			->join("tags", function($join)
				{
					$join->on("files.id", "=", "tags.file_id");
				})	
			->orderBy("tags.confidence")
			->groupBy("tags.value")
	        ->select("files.id", "files.hash", "tags.value")
			->get();
	}
}