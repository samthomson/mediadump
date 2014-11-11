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
			$table->boolean('started');
			$table->datetime('process_from');

			$table->date('created_at');
			$table->date('updated_at');
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
