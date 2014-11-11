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
		$cTagsAdded = 0;

		$oFile = FileModel::find($iFileID);

		$sFilePath = $oFile->rawPath();

		$sFilePath = strtolower($sFilePath);

		$saDirs = explode(DIRECTORY_SEPARATOR, $sFilePath);

		$sFileName = array_pop($saDirs);
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
				$cTagsAdded++;
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
		$cTagsAdded++;

		//
		// file name
		//
		$sFileName = exlode(".", $sFileName)[0];
		$oTag = new TagModel();
		$oTag->file_id = $iFileID;
		$oTag->type = "filename";
		$oTag->value = $sFileName;
		$oTag->save();
		$cTagsAdded++;

		//
		// type
		//
		$oTag = new TagModel();
		$oTag->file_id = $iFileID;
		$oTag->type = "mediatype";
		$oTag->value = "image";
		$oTag->save();
		$cTagsAdded++;

		$oTag = new TagModel();
		$oTag->file_id = $iFileID;
		$oTag->type = "filetype";
		$oTag->value = "jpeg";
		$oTag->save();
		$cTagsAdded++;
		
		

		//
		// exif
		//

		//
		// geo
		//

		//
		// thumbs
		//

		$eFilesRemoved = new EventModel();
		$eFilesRemoved->name = "auto tags added";
		$eFilesRemoved->message = "jpeg processor has added $cTagsAdded tags";
		$eFilesRemoved->value = (string)count($cTagsAdded);
		$eFilesRemoved->save();
		// done?
		$oFile->finishTagging();
	}
}
