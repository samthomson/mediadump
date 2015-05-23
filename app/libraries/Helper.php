<?php

class Helper {
	public static function thumbPath($sSubFolder, $bRelative = false)
	{
		$sPath = '';

		if(!$bRelative){
			$sPath .= public_path();
		}
		$sPath .= DIRECTORY_SEPARATOR."thumbs".DIRECTORY_SEPARATOR;

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
	    //$string = preg_replace('/[\W]+/', ' ', $string);
	    $string = str_replace(" +", " ", $string);
	    return $string;
	}

	//
	// GPS Stuff
	//
	public static function getGps($exifCoord, $hemi) {

		$degrees = count($exifCoord) > 0 ? self::gps2Num($exifCoord[0]) : 0;
		$minutes = count($exifCoord) > 1 ? self::gps2Num($exifCoord[1]) : 0;
		$seconds = count($exifCoord) > 2 ? self::gps2Num($exifCoord[2]) : 0;

		$flip = ($hemi == 'W' or $hemi == 'S') ? -1 : 1;

		return $flip * ($degrees + $minutes / 60 + $seconds / 3600);

	}

	public static function gps2Num($coordPart) {

		$parts = explode('/', $coordPart);

		if (count($parts) <= 0)
			return 0;

		if (count($parts) == 1)
			return $parts[0];

		return floatval($parts[0]) / floatval($parts[1]);
	}

	//
	// file stuff
	//
	public static function bImageCorrupt($sPath)
	{
		if (!is_resource($file = fopen($sPath, 'rb'))) {
	        return TRUE;
	    }
	    // check for the existence of the EOI segment header at the end of the file
	    if (0 !== fseek($file, -2, SEEK_END) || "\xFF\xD9" !== fread($file, 2)) {
	        fclose($file);
	        return TRUE;
	    }
	    fclose($file);
	    return FALSE;
	}
}

?>