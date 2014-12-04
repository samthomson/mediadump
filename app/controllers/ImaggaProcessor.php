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
				echo "imagga processor<br/>";
				// what is the web url?

				$sWebThumbPath = "http://0.cdn.samt.st/lightbox/c859b21ac4bcc9e59335f192e04bb79ab09d4895.jpg";
				// make request
				$service_url = 'http://api.imagga.com/v1/tagging?url='.$sWebThumbPath;

				
				$context = stream_context_create(array(
				    'http' => array(
				        'header'  => "Authorization: Basic " . base64_encode("acc_19db373c6879755:d397f1a6ab0323a3a7a46ebf0a5af625")
				    )
				));

				$jsonurl = $service_url;
				$json = file_get_contents($jsonurl, false, $context);
				$oObj = json_decode($json);
				//print_r($oObj);
				
				if(isset($oObj->results))
				{
					foreach($oObj->results as $oImageResult)
					{
						//print_r($oObj->results);
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



				return false;
			}else{
				$eProcessingFailed = new ErrorModel();
				$eProcessingFailed->location = "imagga processor";
				$eProcessingFailed->message = "no thumb to send to imagga at: $sThumbPath";
				$eProcessingFailed->value = "0";
				$eProcessingFailed->save();
				return false;
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
			return false;
		}
	}
}