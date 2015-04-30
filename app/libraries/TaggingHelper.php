<?php

class TaggingHelper {
	public static function _makeDefaultTag($iFileId)
	{
		$oTag = new TagModel();
		$oTag->file_id = $iFileId;
		$oTag->type = "tag";
		$oTag->value = "*";
		$oTag->save();
	}

	public static function iMakeFilePathTags($saDirs, $iFileId)
	{
		// makes tags, return count of tags made
		$iTagsMade = 0;

		$saDirTags = [];
		$saPunctuationToSkip = ['', ',', '-', ':'];
		//
		// all directorys as tags
		//
		// split dirs with spaces
		foreach ($saDirs as $sDir)
		{
			foreach (explode(" ", $sDir) as $sDirPart) {
				//array_push($saDirTags, $sDirPart);
				if(!in_array($sDirPart, $saPunctuationToSkip)){
					$oTag = new TagModel();
					$oTag->type = "folder term";
					$oTag->file_id = $iFileId;
					$oTag->value = $sDirPart;
					$oTag->save();
					$iTagsMade++;
				}
			}
		}

		return $iTagsMade;
	}

	public static function _QuickTag($iFileId, $sType, $sValue){
		$oTag = new TagModel();
		$oTag->file_id = $iFileId;
		$oTag->type = $sType;
		$oTag->value = $sValue;
		$oTag->save();
	}
}

?>