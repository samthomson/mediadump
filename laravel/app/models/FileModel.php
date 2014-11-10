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

}
