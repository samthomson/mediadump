<?php

class CacheController extends BaseController {

	private static $iDefaultCachePeriod = 60;


	public static function loadSearchTags()
	{
		// create an array of objects to be used in drop down search filter

	}
	public static function getSearchSuggestions()
	{
		$sKey = "tags";
		if (Cache::has($sKey))
		{
			return Cache::get($sKey);
		}else{
			$oaObjectForCache = DB::table("files")
			->join("tags", function($join)
				{
					$join->on("files.id", "=", "tags.file_id");
				})	
			->orderBy("tags.confidence")
			->groupBy("tags.value")
	        ->select("files.id", "files.hash", "tags.value")
			->get();

			if(isset($oaObjectForCache))
				Cache::put($sKey, $oaObjectForCache, self::$iDefaultCachePeriod);

			return $oaObjectForCache;
		}
	}
}