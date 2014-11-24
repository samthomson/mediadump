<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Errors extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('errors', function($table)
		{
			$table->increments('id');
			$table->string('location');
			$table->text('message');
			$table->integer('value');

			$table->datetime('datetime');

			$table->index('location', "errors_location");
			$table->index('datetime', "errors_datetime");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('errors');
	}
}