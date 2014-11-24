<?php

class Auto extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function checkFiles()
	{
		if(self::bAutoOn())
		{
			// get files from db
			$oFiles = FileModel::all();

			//
			// get files locally
			//
			$saFiles = [];

			foreach(File::allFiles(Config::get('app.mediaFolderPath')) as $sFile)
			{
				if(file_exists((string)$sFile)){
					array_push($saFiles, (string)$sFile);
				}
			}

			$saDBFiles = [];
			foreach ($oFiles as $file) {
				array_push($saDBFiles, $file->path);
			}
			$saNewFilesForSystem = array_diff($saFiles, $saDBFiles);
			$saLostFilesFromSystem = array_diff($saDBFiles, $saFiles);

			if(!Config::get('app.keepFilesAfterProcessing')){
				$saLostFilesFromSystem = [];
			}

			$this->addFilesToSystem($saNewFilesForSystem);
			//$this->removeFilesFromSystem($saLostFilesFromSystem);
			// check for differences

			$eFilesFound = new EventModel();
			$eFilesFound->name = "auto files checker ran";
			$eFilesFound->save();

			

			$oStat = new StatModel();
			$oStat->name = "auth files found";
			$oStat->group = "auto";
			$oStat->value = count($saNewFilesForSystem);
			$oStat->save();
		}
	}
	public function processQueue()
	{
		if(self::bAutoOn())
		{
			$qi = QueueModel::getSingleItem();

			if($qi !== null)
				switch($qi->processor)
				{
					case "jpeg":
						$qi->snooze();
						$qi->save();
						if(JPEGProcessor::process($qi->file_id))
						{
							//$qi->delete();
							QueueModel::destroy($qi->id);
							//$qi->save();
						}else{
							$eFilesFound = new EventModel();
							$eFilesFound->name = "auto processor";
							$eFilesFound->message = "jpeg processor failed";
							$eFilesFound->save();

							$oStat = new StatModel();
							$oStat->name = "jpeg processor fail";
							$oStat->group = "auto";
							$oStat->value = 1;
							$oStat->save();
						}
						break;
					default:
						$qi->snooze(1440);
						$qi->save();
						break;
				}

		}
	}

	private function addFilesToSystem($saFiles)
	{
		// takes array of files to add to system and queue
		foreach ($saFiles as $sFilePath) {
			$file = new FileModel();
			$file->path = $sFilePath;
			$file->hash = md5($sFilePath);
			$file->save();

			$sExt = substr(strtolower($sFilePath), strrpos(strtolower($sFilePath), '.')+1);
			
			if($sExt === "jpg" || $sExt === "jpg"){
				$QueueItem = new QueueModel();
				$QueueItem->file_id = $file->id;
				$QueueItem->processor = "jpeg";
				$QueueItem->date_from = date('Y-m-d H:i:s');
				$QueueItem->save();
			}			
		}
	}

	private function removeFilesFromSystem($saFiles)
	{
		// takes array of files to delete from files table and throughout site
		foreach($saFiles as $sFilePath)
		{
			FileModel::where('path', '=', $sFilePath)->delete();
		}
	}
	private static function bAutoOn()
	{
		return Config::get('app.autoOn');
	}
}
