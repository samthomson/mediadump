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

			//print_r($oFile);

			if(file_exists($oFile->path))
			{
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

				$saDirTags = [];
				//
				// all directorys as tags
				//
				// split dirs with spaces
				foreach ($saDirs as $sDir)
				{
					foreach (explode(" ", $sDir) as $sDirPart) {
						//array_push($saDirTags, $sDirPart);
						if($sDirPart !== ""){
							$oTag = new TagModel();
							$oTag->type = "folder term";
							$oTag->file_id = $iFileID;
							$oTag->value = $sDirPart;
							$oTag->save();
							$cTagsAdded++;
						}
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
				$aaThumbPaths = [];

				array_push($aaThumbPaths, array(
					"size" => "large",
					"path" => Helper::thumbPath("large").$oFile->hash.".jpg",
					"width" => null,
					"height" => 1200,
					"aspectRatio" => true
				));
				array_push($aaThumbPaths, array(
					"size" => "medium",
					"path" => Helper::thumbPath("medium").$oFile->hash.".jpg",
					"width" => null,
					"height" => 300,
					"aspectRatio" => true
				));
				array_push($aaThumbPaths, array(
					"size" => "small",
					"path" => Helper::thumbPath("small").$oFile->hash.".jpg",
					"width" => 125,
					"height" => 125,
					"aspectRatio" => false
				));
				array_push($aaThumbPaths, array(
					"size" => "icon",
					"path" => Helper::thumbPath("icon").$oFile->hash.".jpg",
					"width" => 32,
					"height" => 32,
					"aspectRatio" => false
				));


				foreach ($aaThumbPaths as $key => $saMakeThumb) {
					// delete previous thumb of same name
					if(File::exists($saMakeThumb["path"]))
						File::delete($saMakeThumb["path"]);

					$img = Image::make($oFile->path)->orientate();

					if($saMakeThumb["aspectRatio"])
					{
						$img->resize($saMakeThumb["width"], $saMakeThumb["height"], function ($constraint) {
							$constraint->aspectRatio();
						});
					}else{
						$img->fit($saMakeThumb["width"], $saMakeThumb["height"]);
					}

					$img->save($saMakeThumb["path"]);

					if($saMakeThumb["size"] === "medium")
					{
						$oFile->medium_width = $img->width();
						$oFile->medium_height = $img->height();
					}

					$img->destroy();

					if(!File::exists($saMakeThumb["path"])){
						return false;
					}
				}


				//
				// log how many tags were added
				//
				$eFilesRemoved = new EventModel();
				$eFilesRemoved->name = "auto jpeg processor";
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
				$oStat->name = "geodata added";
				$oStat->group = "auto";
				$oStat->value = $cGeoDataAdded;
				$oStat->save();

				$oStat = new StatModel();
				$oStat->name = "jpeg proccess time";
				$oStat->group = "auto";
				$oStat->value = (microtime(true) - $mtStart)*1000;
				$oStat->save();


				// return true, so the processor can delete the work queue item
				return true;
			}else{
				echo "couldn't find: ".$oFile->path;
				// file no longer exists, remove it from system
				$oFile->removeFromSystem();
				return true;
			}

		}
		catch(Exception $ex)
		{
			//print_r($ex);
			$eProcessingFailed = new ErrorModel();
			$eProcessingFailed->name = "error - jpeg processor";
			$eProcessingFailed->message = (string)$ex;
			$eProcessingFailed->value = "0";
			$eProcessingFailed->save();
		}
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
