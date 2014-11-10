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
		// get files from db
		$oFiles = FileModel::all();

		//
		// get files locally
		//
		$saFiles = [];

		foreach(File::allFiles(public_path().Config::get('app.mediaFolderPath')) as $sFile)
		{
			if(file_exists((string)$sFile)){
				array_push($saFiles, (string)$sFile);
			}
		}

		/*
		$rootpath = public_path().'\media';
		echo $rootpath;
		$fileinfos = new RecursiveIteratorIterator(
		    new RecursiveDirectoryIterator($rootpath)
		);
		foreach($fileinfos as $pathname => $fileinfo) {
		    if (!$fileinfo->isFile()){
		    	
		    	continue;
		    }else{
		    	//array_push($saFiles, $fileinfo);
		    }		    
		}
		*/
		//print_r($saFiles);
		$saDBFiles = [];
		foreach ($oFiles as $file) {
			array_push($saDBFiles, $file->path);
		}
		$saNewFilesForSystem = array_diff($saFiles, $saDBFiles);
		$saLostFilesFromSystem = array_diff($saDBFiles, $saFiles);

		$this->addFilesToSystem($saNewFilesForSystem);
		$this->removeFilesFromSystem($saLostFilesFromSystem);
		// check for differences

		$eFilesFound = new EventModel();
		$eFilesFound->name = "auto files found";
		$eFilesFound->value = (string)count($saNewFilesForSystem);
		$eFilesFound->save();

		$eFilesRemoved = new EventModel();
		$eFilesRemoved->name = "auto files removed";
		$eFilesRemoved->value = (string)count($saLostFilesFromSystem);
		$eFilesRemoved->save();
	}
	public function processQueue()
	{
		$aqiQueuedItems = QueueModel::where("started", "=", false);
		foreach ($aqiQueuedItems as $qi) {
			switch($qi->processor)
			{
				case "jpeg":
					JPEGProcessor::process($qi->file_id);
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
			$file->save();

			$sExt = substr(strtolower($sFilePath), strrpos(strtolower($sFilePath), '.')+1);
			
			if($sExt === "jpg" || $sExt === "jpg"){
				$QueueItem = new QueueModel();
				$QueueItem->file_id = $file->id;
				$QueueItem->processor = "jpeg";
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
}