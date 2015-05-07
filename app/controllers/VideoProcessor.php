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
					'ffprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe',
					'timeout' => 0
					)
				);

				$video = null;
				if($sProcessingAction !== "pre-check"){
					$video = $ffmpeg->open($oFile->path);
				}
				

				switch($sProcessingAction){
					case "pre-check":
						// make sure file is under max length and queue it's further processing if ok
						$oFFProbe = FFMpeg\FFProbe::create();

						$mVideoProbe = $oFFProbe
						  ->streams($oFile->path)
						  ->videos()
						  ->first();

						$mDuration = $mVideoProbe->get('duration');
						$mTags = $mVideoProbe->get('tags');

						$fDuration = (float)$mDuration;

						if($fDuration > Helper::_AppProperty("iMaxVideoLengthSeconds"))
						{
							// video is longer than we want to process right now, so queue it, maybe later we do
							$qiVideoQueue = new QueueModel;
							$qiVideoQueue->file_id = $oFile->id;
							$qiVideoQueue->processor = "video-too-long";
							$qiVideoQueue->date_from = date('Y-m-d H:i:s');
							$qiVideoQueue->save();

						}else{
							// some tags from probing
							TaggingHelper::_QuickTag($oFile->id, "duration", (string)$fDuration);
							$cTagsAdded++;


							foreach($mTags as $sKey => $mVideoTag)
							{
								switch($sKey)
								{
									case "creation_time":
										// parse to date and set on file, and as tag?
										$oFile->datetime = $mVideoTag;
										$oFile->save();

										TaggingHelper::_QuickTag($oFile->id, "ffprobe.creation_time", (string)$mVideoTag);
										$cTagsAdded++;
										break;
									case "handler_name":
										TaggingHelper::_QuickTag($oFile->id, "ffprobe.handler_name", (string)$mVideoTag);
										$cTagsAdded++;
										break;
									case "width":
										TaggingHelper::_QuickTag($oFile->id, "ffprobe.width", (string)$mVideoTag);
										$cTagsAdded++;
										break;
									case "height":
										TaggingHelper::_QuickTag($oFile->id, "ffprobe.height", (string)$mVideoTag);
										$cTagsAdded++;
										break;
									case "display_aspect_ratio":
										TaggingHelper::_QuickTag($oFile->id, "ffprobe.aspect_ratio", (string)$mVideoTag);
										$cTagsAdded++;
										break;
									case "r_frame_rate":
										TaggingHelper::_QuickTag($oFile->id, "ffprobe.frame_rate", (string)$mVideoTag);
										$cTagsAdded++;
										break;
									case "codec_name":
										TaggingHelper::_QuickTag($oFile->id, "ffprobe.codec_name", (string)$mVideoTag);
										$cTagsAdded++;
										break;
									case "nb_frames":
										TaggingHelper::_QuickTag($oFile->id, "ffprobe.frames", (string)$mVideoTag);
										$cTagsAdded++;
										break;
									case "channels":
										TaggingHelper::_QuickTag($oFile->id, "ffprobe.channels", (string)$mVideoTag);
										$cTagsAdded++;
										break;
								}
							}

							// queue all other video processors, and store the first couple of tags we have from probing
							$qiVideoQueue = new QueueModel;
							$qiVideoQueue->file_id = $oFile->id;
							$qiVideoQueue->processor = "video-general";
							$qiVideoQueue->date_from = date('Y-m-d H:i:s');
							$qiVideoQueue->save();

							$qiVideoMP4 = new QueueModel;
							$qiVideoMP4->file_id = $oFile->id;
							$qiVideoMP4->processor = "video-mp4";
							$qiVideoMP4->date_from = date('Y-m-d H:i:s');
							$qiVideoMP4->after = $qiVideoQueue->id;
							$qiVideoMP4->save();

							$qiVideoWEBM = new QueueModel;
							$qiVideoWEBM->file_id = $oFile->id;
							$qiVideoWEBM->processor = "video-webm";
							$qiVideoWEBM->date_from = date('Y-m-d H:i:s');
							$qiVideoWEBM->after = $qiVideoMP4->id;
							$qiVideoWEBM->save();

							$qiVideoOGV = new QueueModel;
							$qiVideoOGV->file_id = $oFile->id;
							$qiVideoOGV->processor = "video-ogv";
							$qiVideoOGV->date_from = date('Y-m-d H:i:s');
							$qiVideoOGV->after = $qiVideoWEBM->id;
							$qiVideoOGV->save();


							$qiCleanUp = new QueueModel;
							$qiCleanUp->file_id = $oFile->id;
							$qiCleanUp->processor = "video-finish";
							$qiCleanUp->date_from = date('Y-m-d H:i:s');
							$qiCleanUp->after = $qiVideoOGV->id;
							$qiCleanUp->save();


							$qiElasticIndex = new QueueModel;
							$qiElasticIndex->file_id = $oFile->id;
							$qiElasticIndex->processor = "elasticindex";
							$qiElasticIndex->date_from = date('Y-m-d H:i:s');
							$qiElasticIndex->after = $qiCleanUp->id;
							$qiElasticIndex->save();
						}
						
						break;

					case "general":
						
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
						$sFileExtension = explode(".", $sFileName)[1];
						$sFileName = explode(".", $sFileName)[0];

						TaggingHelper::_QuickTag($oFile->id, "filename", $sFileName);
						$cTagsAdded++;

						//
						// type
						//
						TaggingHelper::_QuickTag($oFile->id, "mediatype", "video");
						$cTagsAdded++;

						if(strtolower($sFileExtension) === "mp4"){
							TaggingHelper::_QuickTag($oFile->id, "filetype", "mp4");
							$cTagsAdded++;
						}

						

						//
						// thumbs
						//

						$aaThumbPaths = [];

						$aaThumbPaths["large"] = array(
							"width" => null,
							"height" => 1200,
							"aspectRatio" => true
						);
						$aaThumbPaths["medium"] = array(
							"width" => null,
							"height" => 300,
							"aspectRatio" => true
						);
						$aaThumbPaths["small"] = array(
							"width" => 125,
							"height" => 125,
							"aspectRatio" => false
						);
						$aaThumbPaths["icon"] = array(
							"width" => 32,
							"height" => 32,
							"aspectRatio" => false
						);

						$sIn = $oFile->path;

						foreach ($aaThumbPaths as $skey => $maMakeThumb) {
							$sOut = Helper::thumbPath($skey).$oFile->hash.".jpg";
							// delete previous thumb of same name
							if(File::exists($sOut))
								File::delete($sOut);
							
							$iH = $maMakeThumb["height"];
							$iW = -1;
							if(!$maMakeThumb["aspectRatio"])
							{
								$iW = $maMakeThumb["width"];
							}

							$sCommand = "ffmpeg -i \"$sIn\" -vframes 1 -filter:v scale=\"$iW:$iH\" \"$sOut\"";
							exec($sCommand);
						}
						// no we've made some thumbnails of the video, store the medium size in the db
						$sReadPath = Helper::thumbPath("medium", false).$oFile->hash.".jpg";
						
						$oImage = Image::make($sReadPath);

						$oFile->medium_width = $oImage->width();
						$oFile->medium_height = $oImage->height();

						$oFile->save();
						
						break;
					case "mp4":
						$oFormat = new FFMpeg\Format\Video\X264();
						$oFormat->setAudioCodec("libvo_aacenc");
						$video->save($oFormat, Helper::thumbPath("mp4").$oFile->hash.'.mp4');
						break;
					case "webm":
						echo "here";
						
						/*$video->save(new FFMpeg\Format\Video\WebM(), Helper::thumbPath("webm").$oFile->hash.'.webm');
*/
						
						$sIn = $oFile->path;
						$sOut = Helper::thumbPath("webm").$oFile->id.".webm";

						$sCmd = "ffmpeg -i \"$sIn\" -b 345k -vcodec libvpx -acodec libvorbis -ab 160000 -f webm -r 15 -g 40 $sOut";
						echo $sCmd;
						exec($sCmd);
						
						/**/
						break;
					case "ogv":
						$video->save(new FFMpeg\Format\Video\Ogg(), Helper::thumbPath("ogv").$oFile->hash.'.ogv');
						break;
					case "finish":
						// remove original file
						$oFile->finishTagging();
						break;
				}


				//
				// log how many tags were added
				//
				$eFilesRemoved = new EventModel();
				$eFilesRemoved->name = "auto video processor";
				$eFilesRemoved->message = "prcoessed a file: ".$sProcessingAction;
				$eFilesRemoved->value = 1;
				$eFilesRemoved->save();



				$oStat = new StatModel();
				$oStat->name = "auto tags added";
				$oStat->group = "auto";
				$oStat->value = $cTagsAdded;
				$oStat->save();

				$oStat = new StatModel();
				$oStat->name = "video ($sProcessingAction) proccess time";
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