<?php

class StatsController extends BaseController {

	public function autoEvents()
	{
		/*
		$dtFrom = Input::get("from");
		$dtTo = Input::get("to");

		//echo "from: $dtFrom, to: $dtTo";
		
		if(isset($dtFrom) && isset($dtTo))
		{
			$aaDatetimeRules = array(
				array('from' => 'date_format:"Y-m-d H:i:s"'),
				array('to' => 'date_format:"Y-m-d H:i:s"')
			);

			$vValidator = Validator::make(Input::all(), $aaDatetimeRules);


			if($vValidator->passes())
			{
				$oaEvents = EventModel::where("datetime", ">", $dtFrom)->where("datetime", "<", $dtTo)->get();
				return Response::json($oaEvents);
			}
		}
		$sTry = "?from=".date('Y-m-d H:i:s', strtotime('-1 day', time()))."&to=".date('Y-m-d H:i:s');
		return Response::make("400: bad params. Try: $sTry", 400);
		*/
	}

	public function autoOverview()
	{
		
	}

	public function makeAutoEvents()
	{
		$dtFrom = Input::get("from");
		$dtTo = Input::get("to");

		//echo "from: $dtFrom, to: $dtTo";
		$oaEvents = null;
		
		if(isset($dtFrom) && isset($dtTo))
		{
			$aaDatetimeRules = array(
				array('from' => 'date_format:"d-m-Y"'),
				array('to' => 'date_format:"d-m-Y"')
			);

			$vValidator = Validator::make(Input::all(), $aaDatetimeRules);


			if($vValidator->passes())
			{
				$oaEvents = EventModel::where("datetime", ">", $dtFrom)->where("datetime", "<", $dtTo)->orderBy("datetime", "desc")->get();
			}else{
				return "bad data";
			}
		}
		return View::make("admin.events")->with("events", $oaEvents)->with("from", $dtFrom)->with("to", $dtTo);
	}
	public static function iTotalFiles()
	{
		return DB::table("files")->count();
	}
	public static function iTotalLiveFiles()
	{
		return DB::table("files")->where("live", "=", 1)->count();
	}
	public static function iTotalTags()
	{
		return DB::table("tags")->count();
	}

	public static function iLastFoundFiles()
	{
		return StatModel::where("name", "=", "auth files found")->orderBy("id", "desc")->take(1)->max("value");
	}
	public static function iLastAverageProcessedFiles()
	{
		return StatModel::where("name", "=", "jpeg processor run count")->orderBy("id", "desc")->take(3)->avg("value");
	}
	public static function iLastAverageProcessTimme()
	{
		return StatModel::where("name", "=", "jpeg proccess time")->orderBy("id", "desc")->take(3)->avg("value");
	}
}