<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Stats extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('stats', function($table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('group');
			$table->integer('value');
			$table->datetime('datetime');

			$table->index('name', "stats_name");
			$table->index('group', "stats_group");
			$table->index('value', "stats_value");
			$table->index('datetime', "stats_datetime");
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('stats');
	}

}
