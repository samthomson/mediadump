<?php

class JPEGProcessor extends BaseController {

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

	public static function process($iFileID)
	{		
		$oFile = FileModel::find($iFileID);

		$sFilePath = $oFile->rawPath();

		$sFilePath = strtolower($sFilePath);

		$saDirs = explode(DIRECTORY_SEPARATOR, $sFilePath);

		array_pop($saDirs);
		//
		// all directorys as tags
		//
		foreach ($saDirs as $sDir) {
			if($sDir !== ""){
				$oTag = new TagModel();
				$oTag->type = "folder";
				$oTag->file_id = $iFileID;
				$oTag->value = $sDir;
				$oTag->save();
			}
		}

		//
		// unique directory path
		//
		$sUniqueDirPath = implode(DIRECTORY_SEPARATOR, $saDirs);
		$oTag = new TagModel();
		$oTag->file_id = $iFileID;
		$oTag->type = "uniquedirectorypath";
		$oTag->value = $sUniqueDirPath;
		$oTag->save();

		//
		// file name
		//

		//
		// type
		//
		/*
		$eFilesFound = new EventModel();
		$eFilesFound->name = "auto files found";
		$eFilesFound->value = (string)count($saNewFilesForSystem);
		$eFilesFound->save();

		$eFilesRemoved = new EventModel();
		$eFilesRemoved->name = "auto files removed";
		$eFilesRemoved->value = (string)count($saLostFilesFromSystem);
		$eFilesRemoved->save();
		*/

		// done?
		$oFile->finishTagging();
	}
}
