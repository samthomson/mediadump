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
		return substr($this->path, 0, strpos($this->path, Config::get('app.mediaFolderPath')));
	}

}
