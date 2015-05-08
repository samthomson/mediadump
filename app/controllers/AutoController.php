<?php

class AutoController extends BaseController {


	public static function checkFiles()
	{
		if(self::bAutoOn())
		{
			// get files from db
			$oFiles = FileModel::all();

			//
			// get files locally
			//
			$saFiles = [];

			echo '<head><meta charset="utf-8"></head>';

			$saAllFiles = File::allFiles(Config::get('app.mediaFolderPath'));
			//$saAllFiles = self::files(Config::get('app.mediaFolderPath'));

			//print_r($saAllFiles);

			foreach($saAllFiles as $sFile)
			{
				//echo "found ", $sFile, "<br/>";
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

			self::addFilesToSystem($saNewFilesForSystem);
			//$this->removeFilesFromSystem($saLostFilesFromSystem);
			// check for differences
			

			if(count($saNewFilesForSystem))
			{
				$eFilesFound = new EventModel();
				$eFilesFound->name = "auto files checker ran";
				$eFilesFound->save();

				$oStat = new StatModel();
				$oStat->name = "auto files found";
				$oStat->group = "auto";
				$oStat->value = count($saNewFilesForSystem);
				$oStat->save();
			}
		}
	}
	public static function files($path)
	{
		//$path   = '.';
		$result = array('files' => array(), 'directories' => array());

		$DirectoryIterator = new RecursiveDirectoryIterator($path);
		$IteratorIterator  = new RecursiveIteratorIterator($DirectoryIterator, RecursiveIteratorIterator::SELF_FIRST);
		foreach ($IteratorIterator as $file) {

		    $path = $file->getRealPath();

		    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			    $path = utf8_decode($path);
			} else {
			    //echo 'This is a server not using Windows!';
			    $path = (string)$path;
			}


		    if ($file->isDir()) {
		        $result['directories'][] = $path;
		    } elseif ($file->isFile()) {
		        $result['files'][] = $path;
		    }
		}
		return $result['files'];
	}
	public static function test()
	{
		echo "gfdgfd";
		//exit();
	}
	public static function processQueue()
	{
		//echo "process fdsfds<br/>";exit();
		$mtProcessQueueStart = microtime(true);

		
		if(self::bAutoOn())
		{
			$cProcessedThisCycle = 0;
			try
			{
				echo "process queue<br/>";
				$iProcessLimit = self::iJpegsThisCycle();
				$bQueueItemsRemaining = true;


				while((self::bTimeForTwoJpegs($mtProcessQueueStart) || $cProcessedThisCycle === 0) && $bQueueItemsRemaining)
				{
					$qi = QueueModel::getSingleItem();				

					if($qi !== null)
					{
						echo 'id:'.$qi->id." ".$qi->processor.'<br/>';
						switch($qi->processor)
						{
							case "jpeg":
								$qi->snooze();
								$qi->save();
								if(JPEGProcessor::process($qi->file_id))
								{
									$qi->done();
								}else{
									$oFailEvent = new EventModel();
									$oFailEvent->name = "auto processor";
									$oFailEvent->message = "jpeg processor failed";
									$oFailEvent->save();

									$oStat = new StatModel();
									$oStat->name = "jpeg processor fail";
									$oStat->group = "auto";
									$oStat->value = 1;
									$oStat->save();
								}
								break;
							case "video-pre-check":
							case "video-general":
							case "video-mp4":
							case "video-webm":
							case "video-ogv":
							case "video-finish":
								/*
								// exit from the loop
								video processing is intensive, so we only want to do one operation per auto cycle
								*/
								$bQueueItemsRemaining = false;
								$qi->snooze();
								$qi->save();
								if(VideoProcessor::process($qi->file_id, str_replace("video-", '', $qi->processor)))
								{
									$qi->done();
								}else{
									$oFailEvent = new EventModel();
									$oFailEvent->name = "auto processor";
									$oFailEvent->message = "video processor failed: ".$qi->processor;
									$oFailEvent->save();

									$oStat = new StatModel();
									$oStat->name = "video processing failed: ".$qi->processor;
									$oStat->group = "auto";
									$oStat->value = 1;
									$oStat->save();
								}
								break;
							case "imagga":
								$qi->snooze(3);
								$qi->save();
								$sResponse = ImaggaProcessor::process($qi->file_id);
								//echo "imagga response: $sResponse<br/>";
								switch($sResponse)
								{
									case "ok":
										$qi->done();
										break;
									case "fail":
										$qi->done();

										$oImaggaFail = new QueueModel;
										$oImaggaFail->file_id = $qi->file_id;
										$oImaggaFail->processor = "imagga_fail";
										$oImaggaFail->save();

										$eFilesFound = new EventModel();
										$eFilesFound->name = "auto processor";
										$eFilesFound->message = "imagga processor failed";
										$eFilesFound->save();

										$oStat = new StatModel();
										$oStat->name = "imagga processor fail";
										$oStat->group = "auto";
										$oStat->value = 1;
										$oStat->save();
										break;
									case "throttle":
										$qi->snooze(1440); // snooze one day
										$qi->save();
										break;
									case "empty":
										$qi->done();

										$oEmptyImagga = new QueueModel;
										$oEmptyImagga->file_id = $qi->file_id;
										$oEmptyImagga->processor = "imagga_empty";
										$oEmptyImagga->save();
										break;
									default:
										$eFilesFound = new EventModel();
										$eFilesFound->name = "auto processor";
										$eFilesFound->message = "imagga processor defaulted: $sResponse";
										$eFilesFound->save();
										break;
								}
								break;
							case "places":
								$qi->snooze(3);
								$qi->save();
								switch(PlacesProcessor::process($qi->file_id))
								{
									case "ok":
										$qi->done();
										break;
									case "fail":
										$eFilesFound = new EventModel();
										$eFilesFound->name = "auto processor";
										$eFilesFound->message = "places processor failed";
										$eFilesFound->save();

										$oStat = new StatModel();
										$oStat->name = "places processor fail";
										$oStat->group = "auto";
										$oStat->value = 1;
										$oStat->save();
										break;
									case "throttle":
										$qi->snooze(1440); // snooze one day
										$qi->save();
										break;
								}
								break;
							case "elasticindex":
								$qi->snooze(3);
								$qi->save();

								if(ElasticSearchController::indexFile($qi->file_id))
								{
									$qi->done();
								}else{
									// failed to index, move to failedindex queue

									$oQueueItem = new QueueModel;

									$oQueueItem->file_id = $qi->file_id;
									$oQueueItem->processor = 'elasticindex_fail';
									$oQueueItem->date_from = date('Y-m-d H:i:s');
									$oQueueItem->save();


									
									$eFilesFound = new EventModel();
									$eFilesFound->name = "auto processor";
									$eFilesFound->message = "elasticindex processor failed";
									$eFilesFound->save();

									$oStat = new StatModel();
									$oStat->name = "elasticindex processor fail";
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
									$qi->done();

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
					}else{
						// no more items in queue
						$bQueueItemsRemaining = false;
					}
				}
			}catch(Exception $eTimedOut)
			{
				echo $eTimedOut;
			}
			if($cProcessedThisCycle > 0)
			{
				$oStat = new StatModel();
				$oStat->name = "auto processor run count";
				$oStat->group = "auto";
				$oStat->value = $cProcessedThisCycle;
				$oStat->save();
			}
		}
	}

	private static function addFilesToSystem($saFiles)
	{
		// takes array of files to add to system and queue
		foreach ($saFiles as $sFilePath) {
			$file = new FileModel;
			$file->path = $sFilePath;
			$file->hash = md5($sFilePath);
			$file->save();

			$sExt = mb_substr(mb_strtolower($sFilePath), strrpos(mb_strtolower($sFilePath), '.')+1);
			
			switch($sExt)
			{
				case "jpg":
				case "jpeg":
					$file->media_type = "image";
					$file->file_type = "jpeg";
					// jpeg processor
					$qiJpegQueue = new QueueModel;
					$qiJpegQueue->file_id = $file->id;
					$qiJpegQueue->processor = "jpeg";
					$qiJpegQueue->date_from = date('Y-m-d H:i:s');
					$qiJpegQueue->save();


					$qiElasticIndex = new QueueModel;
					$qiElasticIndex->file_id = $file->id;
					$qiElasticIndex->processor = "elasticindex";
					$qiElasticIndex->date_from = date('Y-m-d H:i:s');
					$qiElasticIndex->after = $qiJpegQueue->id;
					$qiElasticIndex->save();

					// imagga processor afterwards
					$qiImagga = new QueueModel;
					$qiImagga->file_id = $file->id;
					$qiImagga->processor = "imagga";
					$qiImagga->date_from = date('Y-m-d H:i:s');
					$qiImagga->after = $qiJpegQueue->id;
					$qiImagga->save();

					$qiElasticIndex->file_id = $file->id;
					$qiElasticIndex->processor = "elasticindex";
					$qiElasticIndex->date_from = date('Y-m-d H:i:s');
					$qiElasticIndex->after = $qiImagga->id;
					$qiElasticIndex->save();


					// places processor afterwards
					$qiPlaces = new QueueModel;
					$qiPlaces->file_id = $file->id;
					$qiPlaces->processor = "places";
					$qiPlaces->date_from = date('Y-m-d H:i:s');
					$qiPlaces->after = $qiJpegQueue->id;
					$qiPlaces->save();

					$qiElasticIndex->file_id = $file->id;
					$qiElasticIndex->processor = "elasticindex";
					$qiElasticIndex->date_from = date('Y-m-d H:i:s');
					$qiElasticIndex->after = $qiPlaces->id;
					$qiElasticIndex->save();
					
					break;
					/*
				case "mp4":
					$file->media_type = "video";
					$file->file_type = "mp4";
					// video processor
					$qiVideoCheckQueue = new QueueModel;
					$qiVideoCheckQueue->file_id = $file->id;
					$qiVideoCheckQueue->processor = "video-pre-check";
					$qiVideoCheckQueue->date_from = date('Y-m-d H:i:s');
					$qiVideoCheckQueue->save();
					
					break;
					*/
			}	
			$file->save();
		}
	}

	private static function removeFilesFromSystem($saFiles)
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
			return round(floor(($iMaxMilliseconds*.6)/$iAverageProcessTime));
		else
			return 1;
	}
	private static function bTimeForTwoJpegs($mtStarted)
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