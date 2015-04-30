<?php


class FileModel extends Eloquent {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'files';


	/*
	relations
	*/
	public function tags()
	{
		return $this->hasMany('TagModel', "file_id", "id");
	}
	public function geoData()
	{
		return $this->hasOne('GeoDataModel', "file_id", "id");
	}


	public function rawPath($bLowerCase = false)
	{
		$sPath = str_replace(Config::get('app.mediaFolderPath'), "", $this->path);
		return ($bLowerCase) ? mb_strtolower($sPath) :  $sPath;
	}
	public function saDirectories()
	{
		return explode(DIRECTORY_SEPARATOR, self::rawPath(true));
	}

	public function finishTagging()
	{
		// set to live in db
		$this->live = true;

		// delete on disk maybe.
		if(!Config::get('app.keepFilesAfterProcessing')){
			// delete original
			if(File::exists($this->path))
			{
				//unlink()
				if(!File::delete($this->path)){
					// couldn't delete file, queue it for later deletion when permissions allow
					$QueueItem = new QueueModel();
					$QueueItem->file_id = $this->id;
					$QueueItem->processor = "delete";
					$QueueItem->date_from = date('Y-m-d H:i:s');
					$QueueItem->save();
				}else{
					$this->have_original = false;
				}
			}else{
				echo "file didn't exist";
			}
		}
		$this->save();
	}

	public function removeFromSystem()
	{
		// queue items
		QueueModel::where("file_id", "=", $this->id)->delete();
		// tags
		TagModel::where("file_id", "=", $this->id)->delete();
		// geodata
		GeoDataModel::where("file_id", "=", $this->id)->delete();

		// file
		self::delete();
	}
}