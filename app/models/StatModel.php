<?php


class StatModel extends Eloquent {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	public $timestamps = false; 

	protected $table = 'stats';	


	/*
	overloaded
	*/
	public function save(array $options = array())
	{
		try{
			$this->datetime = date('Y-m-d H:i:s');
	   		parent::save($options);
   		}
   		catch(Exception $e)
   		{
   			// silent fail, duplicate index
   		}
	}
}