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
	public static function check_jpeg($f, $fix=false )
	{
		# [070203]
		# check for jpeg file header and footer - also try to fix it
	    if ( false !== (@$fd = fopen($f, 'r' )) ){
	        if ( fread($fd,2)==chr(255).chr(216) ){
	            fseek ( $fd, -2, SEEK_END );
	            if ( fread($fd,2)==chr(255).chr(217) ){
	                fclose($fd);
	                return true;
	            }else{
	                if ( $fix && fwrite($fd,chr(255).chr(217)) ){return true;}
	                fclose($fd);
	    			echo "2nd fail", "<br/>";
	                return false;
	            }
	        }else{fclose($fd); return false;}
	    }else{
	    	echo "first fail", "<br/>";
	        return false;
	    }
	}

	public static function bImageCorrupt($sPath)
	{

		return false;
		if (!is_resource($file = fopen($sPath, 'rb'))) {
			echo "1st fail", "<br/>";
	        return TRUE;
	    }
	    // check for the existence of the EOI segment header at the end of the file
	    if (0 !== fseek($file, -2, SEEK_END) || "\xFF\xD9" !== fread($file, 2)) {
	        fclose($file);
			echo "2nd fail", "<br/>";
	        return TRUE;
	    }
	    fclose($file);
	    return FALSE;
	}
	public static function completeFiles($path)
	{
		//$path   = '.';
		$result = array('files' => array(), 'directories' => array());

		$DirectoryIterator = new RecursiveDirectoryIterator($path);
		$IteratorIterator  = new RecursiveIteratorIterator($DirectoryIterator, RecursiveIteratorIterator::SELF_FIRST);
		foreach ($IteratorIterator as $file) {

		    $path = $file->getRealPath();

		    /*
		    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			    $path = utf8_decode($path);
			} else {
			    //echo 'This is a server not using Windows!';
			    $path = (string)$path;
			}
*/

		    if ($file->isDir()) {
		        $result['directories'][] = $path;
		    } elseif ($file->isFile()) {
		    	{
		    		/*
		    		$oDateModified = File::lastModified($path);
		    		$time_difference = strtotime('now') - $oDateModified;
		    		echo "time diff: ", $time_difference, "<br/>";

		    		// if file hasn't changed in 60 seconds.
		        	if($time_difference > 60){
		        		*/
		        	/*
		        	if(!Helper::bImageCorrupt($path)){*/
		        	if(self::endsWith(strtolower($path), ".jpg")){
		        		$result['files'][] = $path;
		        	}
		    	}
		    }
		}
		return $result['files'];
	}

	public static function startsWith($haystack, $needle) {
	    // search backwards starting from haystack length characters from the end
	    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}
	public static function endsWith($haystack, $needle) {
	    // search forward starting from end minus needle length characters
	    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}
}

?>