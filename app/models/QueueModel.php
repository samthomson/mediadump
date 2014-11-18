<?php


class QueueModel extends Eloquent {


	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'queue';

	public static function getSingleItem()
	{
		// get items that haven't been started yet and are not scheduled in the future
		$oResults = QueueModel::where("date_from", "<", date('Y-m-d H:i:s'))->take(1)->get();
		if(count($oResults) > 0)
			return $oResults[0];

		return null;
	}
	public static function getItems()
	{
		// get items that haven't been started yet and are not scheduled in the future
		return QueueModel::where("date_from", "<", date('Y-m-d H:i:s'))->get();
	}
	public function snoozeAMinute()
	{
		$this->date_from = date('Y-m-d H:i:s', strtotime("+1 min"));
	}
}