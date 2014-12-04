<?php

class ImaggaProcessor extends BaseController {

	public static function process($iFileID)
	{		
		try
		{
			$mtStart = microtime(true);

			$cTagsAdded = 0;
			
			$oFile = FileModel::find($iFileID);



			if(file_exists(Helper::thumbPath("large").$oFile->hash.".jpg"))
			{
				// what is the web url?

				// make request

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

				// done?
				$oFile->finishTagging();


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
				$eProcessingFailed->name = "error - imagga processor";
				$eProcessingFailed->message = "original file existed b";
				$eProcessingFailed->value = "0";
				$eProcessingFailed->save();
				return false;
			}
		}
		catch(Exception $ex)
		{
			//print_r($ex);
			$eProcessingFailed = new ErrorModel();
			$eProcessingFailed->name = "error - imagga processor";
			$eProcessingFailed->message = (string)$ex;
			$eProcessingFailed->value = "0";
			$eProcessingFailed->save();
			return false;
		}
	}
}