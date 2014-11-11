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
		$sFileName = explode(".", $sFileName)[0];
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
		$img = Image::make($oFile->path)->resize(null, 1200)->save(self::thumbPath("large").$oFile->id.".jpg");
		$img = Image::make($oFile->path)->resize(null, 300)->save(self::thumbPath("medium").$oFile->id.".jpg");
		$img = Image::make($oFile->path)->resize(null, 125)->save(self::thumbPath("small").$oFile->id.".jpg");
		$img = Image::make($oFile->path)->resize(32, 32)->save(self::thumbPath("icon").$oFile->id.".jpg");

		//
		// log how many tags were added
		//
		$eFilesRemoved = new EventModel();
		$eFilesRemoved->name = "auto tags added";
		$eFilesRemoved->message = "jpeg processor has added $cTagsAdded tags";
		$eFilesRemoved->value = (string)count($cTagsAdded);
		$eFilesRemoved->save();
		// done?
		$oFile->finishTagging();
	}
	private static function thumbPath($sSubFolder)
	{

		$sPath = public_path().DIRECTORY_SEPARATOR."thumbs".DIRECTORY_SEPARATOR;

		if(isset($sSubFolder))
			if($sSubFolder !== "")
				$sPath .= $sSubFolder.DIRECTORY_SEPARATOR;

		return $sPath;
	}
}
