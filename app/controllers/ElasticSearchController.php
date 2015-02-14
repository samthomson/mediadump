<?php

class ElasticSearchController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| ElasticSearchController
	|--------------------------------------------------------------------------
	|
	|
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