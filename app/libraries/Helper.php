<?php

class Helper {
	public static function thumbPath($sSubFolder)
	{

		$sPath = public_path().DIRECTORY_SEPARATOR."thumbs".DIRECTORY_SEPARATOR;

		if(isset($sSubFolder))
			if($sSubFolder !== "")
				$sPath .= $sSubFolder.DIRECTORY_SEPARATOR;

		return $sPath;
	}
	public static function iConfidenceThreshold()
	{
		return Config::get('app.confidenceThreshold');
	}
	public static function _AppProperty($sKey)
	{
		return Config::get('app.'.$sKey);
	}
	public static function iMillisecondsSince($mtStart){
		return (microtime(true) - $mtStart)*1000;
	}

	public static function sStripPunctuation($string) {
	    $string = strtolower($string);
	    $string = preg_replace("/[[:punct:]]+/", "", $string);
	    $string = str_replace(" +", " ", $string);
	    return $string;
	}

}

?>