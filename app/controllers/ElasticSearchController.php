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

			$fPerFile = 0;

			if($iFiles > 0){
				$fPerFile = $iTime / $iFiles;
			}

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

					switch($oFile->media_type)
					{
						case "image":
							$params["body"] = array(
								"id" => $oFile->id,
								"hash" => $oFile->hash,
								"media_type" => $oFile->media_type,
								"file_type" => $oFile->file_type,
								"medium_width" => $oFile->medium_width,
								"medium_height" => $oFile->medium_height,
								"datetime" => $oFile->datetime,
								"longtime" => strtotime($oFile->datetime),
								"latitude" => (isset($oFile->geoData->latitude) ? $oFile->geoData->latitude : null),
								"longitude" => (isset($oFile->geoData->longitude) ? $oFile->geoData->longitude : null),
								"elevation" => (isset($oFile->geoData->elevation) ? $oFile->geoData->elevation : null),
								"literal_location" => (isset($oFile->geoData->elevations) ? $oFile->geoData->literal_locations : null),
								"tags" => $aaTags
							);
							break;
						case "video":
							$params["body"] = array(
								"id" => $oFile->id,
								"hash" => $oFile->hash,
								"media_type" => $oFile->media_type,
								"file_type" => $oFile->file_type,
								"medium_width" => $oFile->medium_width,
								"medium_height" => $oFile->medium_height,
								"datetime" => $oFile->datetime,
								"tags" => $aaTags
							);
							break;
					}

					
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
				echo "remove";exit();
				// remove from index
				$deleteParams['index'] = 'mediadump_index';
				$deleteParams['id'] = $oFile->id;
				$client->indices()->delete($deleteParams);
			}
			return true;
		}catch(Exception $e){
			echo $e;
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
}