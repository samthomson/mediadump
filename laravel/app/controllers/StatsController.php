<?php

class StatsController extends BaseController {

	public function autoEvents()
	{
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
		
	}

	public function autoOverview()
	{
		
	}
}