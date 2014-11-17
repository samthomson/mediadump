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
		try
		{
			$mtStart = microtime(true);

			$cTagsAdded = 0;
			$cGeoDataAdded = 0;

			$oFile = FileModel::find($iFileID);

			//
			// default tag
			//
			$oTag = new TagModel();
			$oTag->file_id = $iFileID;
			$oTag->type = "tag";
			$oTag->value = "*";
			$oTag->save();
			$cTagsAdded++;

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
			$data = Image::make($oFile->path)->exif();

			//print_r($data);

			if(isset($data["Make"]))
			{
				$oTag = new TagModel();
				$oTag->file_id = $iFileID;
				$oTag->type = "exif.cameramake";
				$oTag->setValue($data["Make"]);
				$oTag->save();
				$cTagsAdded++;
			}

			if(isset($data["DateTime"]))
			{
				$oFile->datetime = $data["DateTime"];
				$oFile->save();

				$oTag = new TagModel();
				$oTag->file_id = $iFileID;
				$oTag->type = "exif.datetime";
				$oTag->setValue($data["DateTime"]);
				$oTag->save();
				$cTagsAdded++;
			}

			$oGeoData = new GeoDataModel();
			$oGeoData->file_id = $iFileID;

			if(isset($data["GPSLongitude"]) && isset($data["GPSLongitude"]))
			{
				$lon = self::getGps($data["GPSLongitude"], $data['GPSLongitudeRef']);
				$oGeoData->longitude = $lon;
				$oGeoData->save();

				$cGeoDataAdded++;
			}

			if(isset($data["GPSLatitude"]) && isset($data["GPSLatitudeRef"]))
			{
				$lat = self::getGps($data["GPSLatitude"], $data['GPSLatitudeRef']);
				$oGeoData->latitude = $lat;
				$oGeoData->save();

				$cGeoDataAdded++;
			}


				

			//
			// geo
			//

			//
			// thumbs
			//
			$saThumbPaths = array(
				self::thumbPath("large").$oFile->id.".jpg",
				self::thumbPath("medium").$oFile->id.".jpg",
				self::thumbPath("small").$oFile->id.".jpg",
				self::thumbPath("icon").$oFile->id.".jpg"
				);

			
			foreach ($saThumbPaths as $sPath) {
				// delete previous thumb of same name
				if(File::exists($sPath))
					File::delete($sPath);
			}
			

			$img = Image::make($oFile->path)->orientate()->resize(null, 1200, function ($constraint) {
			    $constraint->aspectRatio();
			})->save(self::thumbPath("large").$oFile->id.".jpg")->destroy();
			if(!File::exists(self::thumbPath("large").$oFile->id.".jpg"))
				return false;

			$img = Image::make($oFile->path)->orientate()->resize(null, 300, function ($constraint) {
			    $constraint->aspectRatio();
			})->save(self::thumbPath("medium").$oFile->id.".jpg");


			$oFile->medium_width = $img->width();
			$oFile->medium_height = $img->height();

			$img->destroy();
			if(!File::exists(self::thumbPath("medium").$oFile->id.".jpg"))
				return false;

			$img = Image::make($oFile->path)->orientate()->resize(null, 125, function ($constraint) {
			    $constraint->aspectRatio();
			})->save(self::thumbPath("small").$oFile->id.".jpg")->destroy();
			if(!File::exists(self::thumbPath("small").$oFile->id.".jpg"))
				return false;

			$img = Image::make($oFile->path)->orientate()->resize(32, 32, function ($constraint) {
			})->save(self::thumbPath("icon").$oFile->id.".jpg")->destroy();
			if(!File::exists(self::thumbPath("icon").$oFile->id.".jpg"))
				return false;


			//
			// log how many tags were added
			//
			$eFilesRemoved = new EventModel();
			$eFilesRemoved->name = "auto tags added";
			$eFilesRemoved->message = "jpeg processor has added $cTagsAdded tags";
			$eFilesRemoved->value = (string)$cTagsAdded;
			$eFilesRemoved->save();

			$eFilesRemoved = new EventModel();
			$eFilesRemoved->name = "geodata added";
			$eFilesRemoved->message = "jpeg processor has added $cGeoDataAdded pieces of geodata";
			$eFilesRemoved->value = (string)$cGeoDataAdded;
			$eFilesRemoved->save();

			// done?
			$oFile->finishTagging();


			$oEvent = new EventModel();
			$oEvent->name = "jpeg process time";
			$oEvent->message = "time to proess: ".$oFile->id." as jpeg";
			$oEvent->value = (microtime(true) - $mtStart)*1000;
			$oEvent->save();


			// return true, so the processor can delete the work queue item
			return true;
		}
		catch(Exception $ex)
		{
			//print_r($ex);
			$eProcessingFailed = new EventModel();
			$eProcessingFailed->name = "error - jpeg processor";
			$eProcessingFailed->message = (string)$ex;
			$eProcessingFailed->value = "0";
			$eProcessingFailed->save();
		}
	}
	private static function thumbPath($sSubFolder)
	{

		$sPath = public_path().DIRECTORY_SEPARATOR."thumbs".DIRECTORY_SEPARATOR;

		if(isset($sSubFolder))
			if($sSubFolder !== "")
				$sPath .= $sSubFolder.DIRECTORY_SEPARATOR;

		return $sPath;
	}

	private static function getGps($exifCoord, $hemi) {

		$degrees = count($exifCoord) > 0 ? self::gps2Num($exifCoord[0]) : 0;
		$minutes = count($exifCoord) > 1 ? self::gps2Num($exifCoord[1]) : 0;
		$seconds = count($exifCoord) > 2 ? self::gps2Num($exifCoord[2]) : 0;

		$flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

		return $flip * ($degrees + $minutes / 60 + $seconds / 3600);

	}

	private static function gps2Num($coordPart) {

		$parts = explode('/', $coordPart);

		if (count($parts) <= 0)
			return 0;

		if (count($parts) == 1)
			return $parts[0];

		return floatval($parts[0]) / floatval($parts[1]);
	}
}
