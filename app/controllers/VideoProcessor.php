<?php
class OpenMp4 extends FFMpeg\Format\Video\DefaultVideo
{
    public function __construct($audioCodec = 'aac', $videoCodec = 'libx264')
    {
        $this
            ->setAudioCodec($audioCodec)
            ->setVideoCodec($videoCodec);
    }

    public function supportBFrames()
    {
        return false;
    }

    public function getAvailableAudioCodecs()
    {
        return array('aac');
    }

    public function getAvailableVideoCodecs()
    {
        return array('libx264');
    }
}


class VideoProcessor extends BaseController {

	public static function testProcess($iFileId)
	{

		$oTestVideo = FileModel::find($iFileId);

		// make array of files created (to delete after if one part of process fails)
		
		
		// create mp4
		// create ogv
		// create webm

		// create stills
	}

	public static function process($iFileId, $sProcessingAction)
	{		
		try
		{
			$mtStart = microtime(true);

			$cTagsAdded = 0;

			$oFile = FileModel::find($iFileId);

			//print_r($oFile);

			if(file_exists($oFile->path))
			{

				$ffmpeg = FFMpeg\FFMpeg::create(array(
					'ffmpeg.binaries'  => 'C:/ffmpeg/bin/ffmpeg.exe',
					'ffprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe'
					)
				);

				$video = $ffmpeg->open($oFile->path);
				

				switch($sProcessingAction){
					case "general":
					case "all":
						$video->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(5))->save(Helper::thumbPath("test").$oFile->id.".jpg");



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
						TaggingHelper::_QuickTag($oFile->id, "mediatype", "video");
						$cTagsAdded++;


						TaggingHelper::_QuickTag($oFile->id, "filetype", "jpeg");
						$cTagsAdded++;

						

						//
						// thumbs
						//
						/*
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
						*/
					case "mp4":
					case "all":
						$oFormat = new FFMpeg\Format\Video\X264();
						$oFormat->setAudioCodec("libvo_aacenc");
						$video->save($oFormat, Helper::thumbPath("test").$oFile->id.'.mp4');
					case "webm":
					case "all":
						$video->save(new FFMpeg\Format\Video\WebM(), Helper::thumbPath("test").$oFile->id.'.webm');
					case "ogv":
					case "all":
						$video->save(new FFMpeg\Format\Video\Ogg(), Helper::thumbPath("test").$oFile->id.'.ogv');
				}


				//
				// log how many tags were added
				//
				$eFilesRemoved = new EventModel();
				$eFilesRemoved->name = "auto video processor";
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
				$oStat->name = "video proccess time";
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
			$eProcessingFailed->location = "video processor";
			$eProcessingFailed->message = (string)$ex;
			$eProcessingFailed->value = "0";
			$eProcessingFailed->save();
		}
	}
}