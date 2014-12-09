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

}

?>