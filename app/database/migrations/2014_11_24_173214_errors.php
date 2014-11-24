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
			$table->int('value');

			$table->datetime('datetime');

			$table->index("name", "group", "value");
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
