<?php

class PlacesProcessor extends BaseController {

	public static function process($iFileID)
	{		
		try
		{
			$mtStart = microtime(true);

			$cTagsAdded = 0;
			
			$oFile = FileModel::find($iFileID);


			$sThumbPath = Helper::thumbPath("large").$oFile->hash.".jpg";
			$oGeoData = $oFile->geoData();

			$bHasLatLon = false;

			if(isset($oGeoData))
			{
				if(isset($oGeoData->latitude) && isset($oGeoData->longitude))
				{
					if($oGeoData->latitude != 0 && $oGeoData->longitude != 0)
					{
						$bHasLatLon = true;
					}
				}
			}


			if($bHasLatLon)
			{
				// make request
				$sPlacesURL  = 'http://maps.googleapis.com/maps/api/geocode/json?latlng='.urlencode($oGeoData->latitude).','.urlencode($oGeoData->longitude).'&sensor=false';

				echo $sPlacesURL."<br/><br/>";


				$json = file_get_contents($sPlacesURL);


				list($version, $status_code, $msg) = explode(' ',$http_response_header[0], 3);

				// Check the HTTP Status code
				switch($status_code)
				{
				    case 200:
						$oObj = json_decode($json);

						if(isset($oObj->status)){

							switch($oObj->status)
							{

								case "OK":
								case "ZERO_RESULTS":
								case "INVALID_REQUEST":
									if(isset($oObj->results))
									{
										$saPlaces = [];
										$saComponents = [];

										foreach($oObj->results as $oImageResult)
										{
											// get all distinct formatted addresses
											if(isset($oImageResult->formatted_address))
											{
												if(!in_array($oImageResult->formatted_address, $saPlaces))
												{
													array_push($saPlaces, $oImageResult->formatted_address);
												}

											}
											// get all distinct address components
											if(isset($oImageResult->address_components))
											{
												foreach($oImageResult->address_components as $jsonAddressComponent){

													if(!in_array($jsonAddressComponent->long_name, $saComponents))
													{
														array_push($saComponents, $jsonAddressComponent->long_name);
													}
												}
											}
										}			
										foreach ($saPlaces as $value) {
											echo "$value<br/>";
													
											$oNewTag = new TagModel();
											$oNewTag->file_id = $iFileID;
											$oNewTag->type = "places.formattedaddress";
											$oNewTag->setValue(Helper::sStripPunctuation($value));
											$oNewTag->confidence = 65;
											$oNewTag->save();
											$cTagsAdded++;
										}
		
										foreach ($saComponents as $value) {
											echo "$value<br/>";
													
											$oNewTag = new TagModel();
											$oNewTag->file_id = $iFileID;
											$oNewTag->type = "places.addresscomponent";
											$oNewTag->setValue(Helper::sStripPunctuation($value));
											$oNewTag->confidence = 75;
											$oNewTag->save();
											$cTagsAdded++;
										}

									
													

										// for each tag back, add to db
										//$cTagsAdded++;

										//
										// log how many tags were added
										//
										$eFilesRemoved = new EventModel();
										$eFilesRemoved->name = "auto places processor";
										$eFilesRemoved->message = "prcoessed a file";
										$eFilesRemoved->value = 1;
										$eFilesRemoved->save();



										$oStat = new StatModel();
										$oStat->name = "auto tags added";
										$oStat->group = "auto";
										$oStat->value = $cTagsAdded;
										$oStat->save();

										$oStat = new StatModel();
										$oStat->name = "places proccess time";
										$oStat->group = "auto";
										$oStat->value = (microtime(true) - $mtStart)*1000;
										$oStat->save();
									}	
									break;
								case "REQUEST_DENIED":
								case "OVER_QUERY_LIMIT":
								case "UNKNOWN_ERROR":
									return "throttle";
									break;
							}
						}
				        break;
				    default:
				        return "fail";
				        break;
				}

				
			}else{
				$eProcessingFailed = new ErrorModel();
				$eProcessingFailed->location = "places processor";
				$eProcessingFailed->message = "no geo to send to places at: $sThumbPath (file: $iFileID)";
				$eProcessingFailed->value = "0";
				$eProcessingFailed->save();
				return "fail";
			}
			// still here? everything was fine
			return "ok";
		}
		catch(Exception $ex)
		{
			print_r($ex);
			$eProcessingFailed = new ErrorModel();
			$eProcessingFailed->location = "places processor";
			$eProcessingFailed->message = (string)$ex;
			$eProcessingFailed->value = "0";
			$eProcessingFailed->save();
			return "fail";
		}
	}
}