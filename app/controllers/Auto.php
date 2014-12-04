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

			if(count($saNewFilesForSystem))
			{
				$oStat = new StatModel();
				$oStat->name = "auth files found";
				$oStat->group = "auto";
				$oStat->value = count($saNewFilesForSystem);
				$oStat->save();
			}
		}
	}
	public function processQueue()
	{
		$mtProcessQueueStart = microtime(true);

		if(self::bAutoOn())
		{
			$cProcessedThisCycle = 0;
			try
			{
				////echo "process queue<br/>";
				$iProcessLimit = self::iJpegsThisCycle();

				while(self::bTimeForTwoJpegs($mtProcessQueueStart) || $cProcessedThisCycle === 0)
				{
					$qi = QueueModel::getSingleItem();

					if($qi !== null)
						switch($qi->processor)
						{
							case "jpeg":
								////echo "jpeg processor<br/>";
								$qi->snooze();
								$qi->save();
								if(JPEGProcessor::process($qi->file_id))
								{
									QueueModel::destroy($qi->id);
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
							case "delete":
								////echo "delete processor<br/>";
								$qi->snooze();
								$qi->save();
								if(DeleteProcessor::process($qi->file_id))
								{
									QueueModel::destroy($qi->id);

									$eFilesFound = new EventModel();
									$eFilesFound->name = "auto processor";
									$eFilesFound->message = "delete processor deleleted a file";
									$eFilesFound->save();
								}else{
									$eFilesFound = new EventModel();
									$eFilesFound->name = "auto processor";
									$eFilesFound->message = "delete processor failed";
									$eFilesFound->save();
								}
								break;
							default:
								$qi->snooze(1440); // snooze one day
								$qi->save();
								break;
						}
					$cProcessedThisCycle++;
				}
			}catch(Exception $eTimedOut)
			{
			}
			$oStat = new StatModel();
			$oStat->name = "jpeg processor run count";
			$oStat->group = "auto";
			$oStat->value = $cProcessedThisCycle;
			$oStat->save();

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
			
			switch($sExt)
			{
				case "jpg":
				case "jpeg":
					// jpeg processor
					$qiJpegQueue = new QueueModel();
					$qiJpegQueue->file_id = $file->id;
					$qiJpegQueue->processor = "jpeg";
					$qiJpegQueue->date_from = date('Y-m-d H:i:s');
					$qiJpegQueue->save();

					// imagga processor afterwards
					$qiImagga = new QueueModel();
					$qiImagga->file_id = $file->id;
					$qiImagga->processor = "imagga";
					$qiImagga->date_from = date('Y-m-d H:i:s');
					$qiImagga->after = $qiJpegQueue->id;
					$qiImagga->save();
					break;
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
	private static function iJpegsThisCycle()
	{
		// average process time of last files
		$iAverageProcessTime = StatModel::where("name", "=", "jpeg proccess time")->orderBy("id", "desc")->take(3)->avg("value");
		// max execution time
		$iMaxMilliseconds = ini_get('max_execution_time') * 1000;

		// how many files should we attempt to process now
		if(isset($iAverageProcessTime))
			return round(floor(($iMaxMilliseconds*.7)/$iAverageProcessTime));
		else
			return 1;
	}
	private function bTimeForTwoJpegs($mtStarted)
	{
		// estiamtes current jpeg process time, gets time remaining, returns true if less than
		$iMaxMilliseconds = ini_get('max_execution_time') * 1000;

		$iCurrentExecutionTime = (microtime(true) - $mtStarted)*1000;

		$iAverageProcessTime = StatModel::where("name", "=", "jpeg proccess time")->orderBy("id", "desc")->take(3)->avg("value");

		if(($iAverageProcessTime * 2) < ($iMaxMilliseconds - $iCurrentExecutionTime))
			return true;
		else
			return false;
	}
}
