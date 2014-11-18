<?php


class TagModel extends Eloquent {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */

	public $timestamps = false;
	
	protected $table = 'tags';

	public function setValue($sValue)
	{
		$this->value = strtolower($sValue);
	}
	
}
