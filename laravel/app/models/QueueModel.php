<?php


class QueueModel extends Eloquent {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'queue';

	public static function getItems()
	{
		// get items that haven't been started yet and are not scheduled in the future
		return QueueModel::where("started", "=", "0")->where("date_from", "<", date('Y-m-d H:i:s'))->get();
	}
}