<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Queue extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('queue', function($table)
		{
			$table->increments('id');
			$table->string('file_id');
			$table->string('processor');
			$table->datetime('date_from');
			$table->integer('after')->default(-1);

			$table->date('created_at');
			$table->date('updated_at');

			$table->unique(array('file_id', 'processor', 'after'));

			$table->index('file_id', "queue_file_id");
			$table->index('processor', "queue_processor");
			$table->index('date_from', "queue_datefrom");
			$table->index('after', "queue_after");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('queue');
	}

}
