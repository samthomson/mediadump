<?php


class FileModel extends Eloquent {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'files';


	public function rawPath()
	{
		return str_replace(Config::get('app.mediaFolderPath'), "", $this->path);
	}
	public function finishTagging()
	{
		// set to live in db
		$this->live = true;
		$this->save();

		// delete on disk maybe.
		if(!Config::get('app.keepFilesAfterProcessing')){
			// delete original
			echo "delete: ".$this->path;
			if(File::exists($this->path))
				File::delete($this->path);
		}
	}

}
