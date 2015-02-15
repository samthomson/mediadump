<?php

class ElasticSearchController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| ElasticSearchController
	|--------------------------------------------------------------------------
	|
	|
	*/
	public static function createIndex()
	{
		try{
			$client = new Elasticsearch\Client();
			$indexParams['index']  = 'mediadump_index';    //index

			$client->indices()->create($indexParams);
		}catch(Exception $e){echo "createing index failed: $e";}
	}
	public static function deleteIndex()
	{
		$client = new Elasticsearch\Client();
		$deleteParams['index'] = 'mediadump_index';
		$client->indices()->delete($deleteParams);		
	}

	public static function scheduleFullReindex()
	{
		try{
			$mtStart = microtime(true);



			$iLimit = 100000;

			$oaFiles = FileModel::take($iLimit)->where("live", "=", 1)->get();

			
			foreach($oaFiles as $oFile){

				//$b = ElasticSearchController::indexFile($oFile->id);
				$oQueueItem = new QueueModel;

				$oQueueItem->file_id = $oFile->id;
				$oQueueItem->processor = 'elasticindex';
				$oQueueItem->date_from = date('Y-m-d H:i:s');
				try{
					$oQueueItem->save();
				}catch(Exception $e){}
			}			

			$iFiles = count($oaFiles);
			$iTime = Helper::iMillisecondsSince($mtStart);
			$fPerFile = $iTime / $iFiles;
			echo "indexed files ($iFiles/$iLimit) @ $iTime ms, av $fPerFile per file";

		}catch(Exception $e){
			echo $e;
		}
	}

	public static function indexFile($iFileId)
	{
		// make sure elastic search index is representitive of this file
		// if the file is live, index it, if not make sure it doesn't exist
		try
		{
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
					$params["index"] = "mediadump_index";
					$params["type"] = "file";
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
				$deleteParams['index'] = 'mediadump_index';
				$deleteParams['id'] = $oFile->id;
				$client->indices()->delete($deleteParams);
			}
			return true;
		}catch(Exception $e){
			return false;
		}
	}

	public static function delete()
	{
		$client = new Elasticsearch\Client();

		$deleteParams['index'] = 'my_index';
		$deleteParams['id'] = 2509;
		$client->indices()->delete($deleteParams);

	}
		
/*
	public static function search()
	{
		try{
			$client = new Elasticsearch\Client();

			$saResults = [];


			$searchParams['index'] = 'mediadump_index';
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

			$iMs = -1;
			$iCount = 0;

			if(isset($retDoc["took"]))
				$iMs = $retDoc["took"];

			$oaResults = [];

			if(isset($retDoc["hits"]["hits"]))
			{
				$iCount = count($retDoc["hits"]["hits"]);



				foreach($retDoc["hits"]["hits"] as $oHit){
					//print_r($oHit);
					
					if(false)
						echo $oHit["_source"]["id"].": ".$oHit["_source"]["datetime"]."<br/>";


					$saResults[$oHit["_source"]["id"]] = $oHit["_source"]["hash"];

					//array_push($saResults, $oHit["_source"]["hash"]);
					array_push($oaResults, [
						"id" => $oHit["_source"]["id"],
						"hash" => $oHit["_source"]["hash"],
						"latitude" => $oHit["_source"]["latitude"],
						"longitude" => $oHit["_source"]["longitude"],
						"width" => 450,
						"height" => 300
						]);
				}
			}

			//
			// redner
			//
			//print_r($retDoc);

			$oaInfo = [
				"speed" => $iMs,
				"count" => $iCount,
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
	*/
}