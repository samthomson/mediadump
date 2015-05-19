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

			$table->float('latitude', 12, 8);
			$table->float('longitude', 12, 8);
			$table->float('elevation', 8, 8);
			$table->string('literal_locations');

			$table->index('file_id', "geodata_fileid");
			$table->index('latitude', "geodata_latitude");
			$table->index('longitude', "geodata_longitude");

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
