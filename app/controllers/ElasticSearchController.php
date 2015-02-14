<?php

class ElasticSearchController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| ElasticSearchController
	|--------------------------------------------------------------------------
	|
	|
	*/
	
/*
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
	*/

	public static function indexFile($iFileId)
	{
		// make sure elastic search index is representitive of this file
		// if the file is live, index it, if not make sure it doesn't exist
		$oFile = FileModel::find($iFileId);
		$bRemove = false;
		$client = new Elasticsearch\Client();

		if(isset($oFile)){
			if($oFile->live == 1){
				//
				// re-index
				//

				$aaTags = [];

				foreach($oFile->tags as $oTag){

					$oTagIndexes = [];

					$oTagIndexes = array(
						"type" => $oTag->type,
						"value" => $oTag->value,
						"confidence" => $oTag->confidence);


					// if tag type contains dot, tag it's group
					// i.e. 'places' from 'places.addresscomponent'
					$saGroupParts = explode(".", $oTag->type);
					if(count($saGroupParts) > 1){
						$oTagIndexes["group"] = $saGroupParts[0];
					}

					array_push($aaTags, $oTagIndexes);
				}
				
				$params = array();
				$params["body"] = array(
					"id" => $oFile->id,
					"hash" => $oFile->hash,
					"medium_width" => $oFile->geoData->medium_width,
					"medium_height" => $oFile->geoData->medium_height,
					"datetime" => $oFile->datetime,
					"longtime" => strtotime($oFile->datetime),
					"latitude" => $oFile->geoData->latitude,
					"longitude" => $oFile->geoData->longitude,
					"elevation" => $oFile->geoData->elevation,
					"tags" => $aaTags
				);
				$params["index"] = "test_index";
				$params["type"] = "my_type";
				$params["id"] = $oFile->id;

				$ret = $client->index($params);
			}else{
				$bRemove = true;
			}
		}else{
			$bRemove = true;
		}
		if($bRemove){
			// remove from index
			$deleteParams['index'] = 'my_index';
			$deleteParams['id'] = $oFile->id;
			$client->indices()->delete($deleteParams);
		}
		return true;
	}

	public static function delete()
	{
		$client = new Elasticsearch\Client();

		$deleteParams['index'] = 'my_index';
		$deleteParams['id'] = 2509;
		$client->indices()->delete($deleteParams);

	}
		

	public static function search()
	{
		try{
			$client = new Elasticsearch\Client();

			$saResults = [];


			$searchParams['index'] = 'test_index';
			$searchParams['size'] = 100;


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
					"longitude" => $oHit["_source"]["longitude"],/*
					"tags" => $oHit["_source"]["tags"],*/
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