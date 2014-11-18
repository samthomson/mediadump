<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GeoData extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('geodata', function($table)
		{
			$table->increments('id');
			$table->integer('file_id');

			$table->float('latitude');
			$table->float('longitude');
			$table->float('elevation');
			$table->string('literal_locations');

			$table->index(array('file_id', 'latitude', 'longitude'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('geodata');
	}

}
