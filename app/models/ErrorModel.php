<?php


class ErrorModel extends Eloquent {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	public $timestamps = false; 

	protected $table = 'errors';	

	/* overloads */
	public function save(array $options = array())
	{
		$this->datetime = date("Y-m-d H:i:s");
	   	parent::save($options);
	}
}