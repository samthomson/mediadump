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
				TaggingHelper::_makeDefaultTag($oFile->id);
				$cTagsAdded++;

				$sFilePath = $oFile->rawPath(true);

				$saDirs = $oFile->saDirectories();

				$sFileName = array_pop($saDirs);

				$cTagsAdded += TaggingHelper::iMakeFilePathTags($saDirs, $oFile->id);

				// unique directory path
				$sUniqueDirPath = implode(DIRECTORY_SEPARATOR, $saDirs);

				TaggingHelper::_QuickTag($oFile->id, "uniquedirectorypath", $sUniqueDirPath);
				$cTagsAdded++;

				//
				// file name
				//
				$sFileName = explode(".", $sFileName)[0];

				TaggingHelper::_QuickTag($oFile->id, "filename", $sFileName);
				$cTagsAdded++;

				//
				// type
				//
				TaggingHelper::_QuickTag($oFile->id, "mediatype", "image");
				$cTagsAdded++;


				TaggingHelper::_QuickTag($oFile->id, "filetype", "jpeg");
				$cTagsAdded++;

				//
				// exif
				//
				// read exif, unless it's corrupt...
					
				//if(exif_imagetype($oFile->path) == false)
				if(Helper::bImageCorrupt($oFile->path))
				{
					echo "corrupt image<br/>";
					// corrupt image
					// problem reading exif data, not the end of the world, log it and continue to attempt thumb generation
					$qiElasticIndex = new QueueModel;
					$qiElasticIndex->file_id = $oFile->id;
					$qiElasticIndex->processor = "exif_fail";
					$qiElasticIndex->date_from = date('Y-m-d H:i:s');
					$qiElasticIndex->after = 0;
					$qiElasticIndex->save();
					
				}
				else
				{
					
					//$data = Image::make($oFile->path)->exif();
					$data = exif_read_data($oFile->path);

					print_r($data);
					if(isset($data["Make"]))
					{
						TaggingHelper::_QuickTag($oFile->id, "exif.cameramake", $data["Make"]);
						$cTagsAdded++;
					}

					if(isset($data["DateTime"]))
					{
						$oFile->datetime = $data["DateTime"];
						$oFile->save();

						TaggingHelper::_QuickTag($oFile->id, "exif.datetime", $data["DateTime"]);
						$cTagsAdded++;
					}

					//
					// geo
					//

					$oGeoData = new GeoDataModel();
					$oGeoData->file_id = $iFileID;

					if(isset($data["GPSLongitude"]) && isset($data["GPSLongitudeRef"]))
					{
						$lon = Helper::getGps($data["GPSLongitude"], $data['GPSLongitudeRef']);
						echo "lon: ", $lon, "<br/>";
						$oGeoData->longitude = $lon;
						echo"lon: ",  $oGeoData->longitude, "<br/>";
						$oGeoData->save();
						echo"lon: ",  $oGeoData->longitude, "<br/>";

						$cGeoDataAdded++;
					}

					if(isset($data["GPSLatitude"]) && isset($data["GPSLatitudeRef"]))
					{
						$lat = Helper::getGps($data["GPSLatitude"], $data['GPSLatitudeRef']);
						echo $lat, "<br/>";
						$oGeoData->latitude = $lat;
						$oGeoData->save();

						$cGeoDataAdded++;
					}
				}
				


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
			$eProcessingFailed->location = "jpeg processor";
			$eProcessingFailed->message = (string)$ex;
			$eProcessingFailed->value = "0";
			$eProcessingFailed->save();
		}
	}
}
