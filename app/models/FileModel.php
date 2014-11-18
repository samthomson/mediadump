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


	public function rawPath()
	{
		return str_replace(Config::get('app.mediaFolderPath'), "", $this->path);
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
				File::delete($this->path);
				$this->have_original = false;
			}
		}
		$this->save();
	}

}
