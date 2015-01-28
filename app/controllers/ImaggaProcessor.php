<?php

class ImaggaProcessor extends BaseController {

	public static function process($iFileID)
	{		
		try
		{
			$mtStart = microtime(true);

			$cTagsAdded = 0;
			
			$oFile = FileModel::find($iFileID);


			$sThumbPath = Helper::thumbPath("large").$oFile->hash.".jpg";
			if(file_exists($sThumbPath))
			{
				// what is the web url?

				$sWebThumbPath = "http://mediadump.samt.st/thumbs/large/".$oFile->hash.".jpg";

				// make request
				$service_url = 'http://api.imagga.com/v1/tagging?url='.$sWebThumbPath;

				$sKey = Helper::_AppProperty('imaggaKey');
				$sSecret = Helper::_AppProperty('imaggaSecret');
				
				$context = stream_context_create(array(
				    'http' => array(
				        'header'  => "Authorization: Basic " . base64_encode($sKey.":".$sSecret)
				    )
				));

				$jsonurl = $service_url;
				$json = file_get_contents($jsonurl, false, $context);


				list($version,$status_code,$msg) = explode(' ',$http_response_header[0], 3);

				// Check the HTTP Status code
				switch($status_code)
				{
				    case 200:
						$oObj = json_decode($json);

						if(isset($oObj->results))
						{
							foreach($oObj->results as $oImageResult)
							{
								if(isset($oImageResult->tags))
								{
									foreach($oImageResult->tags as $oTag){
										$oTag = (array)$oTag;
										
										$oNewTag = new TagModel();
										$oNewTag->file_id = $iFileID;
										$oNewTag->type = "imagga";
										$oNewTag->setValue($oTag["tag"]);
										$oNewTag->confidence = $oTag["confidence"];
										$oNewTag->save();
										$cTagsAdded++;
									}
								}
							}				
						

							// for each tag back, add to db
							//$cTagsAdded++;

							//
							// log how many tags were added
							//
							$eFilesRemoved = new EventModel();
							$eFilesRemoved->name = "auto imagga processor";
							$eFilesRemoved->message = "prcoessed a file";
							$eFilesRemoved->value = 1;
							$eFilesRemoved->save();



							$oStat = new StatModel();
							$oStat->name = "auto tags added";
							$oStat->group = "auto";
							$oStat->value = $cTagsAdded;
							$oStat->save();

							$oStat = new StatModel();
							$oStat->name = "imagga proccess time";
							$oStat->group = "auto";
							$oStat->value = (microtime(true) - $mtStart)*1000;
							$oStat->save();
						}	



						return "ok";
				        break;
				    default:
				         $error_status="Undocumented error: " . $status_code;
				         return "throttle";
				         break;
				}



				
				//print_r($oObj);


				
				
			}else{
				$eProcessingFailed = new ErrorModel();
				$eProcessingFailed->location = "imagga processor";
				$eProcessingFailed->message = "no thumb to send to imagga at: $sThumbPath";
				$eProcessingFailed->value = "0";
				$eProcessingFailed->save();
				return "fail";
			}
		}
		catch(Exception $ex)
		{
			print_r($ex);
			$eProcessingFailed = new ErrorModel();
			$eProcessingFailed->location = "imagga processor";
			$eProcessingFailed->message = (string)$ex;
			$eProcessingFailed->value = "0";
			$eProcessingFailed->save();
			return "fail";
		}
	}
}