<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDependenciesToQueueItems extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('queue', function(Blueprint $table)
		{
			$table->integer('after')->default(-1); // index of another queue item which must be done first
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('queue', function(Blueprint $table)
		{
			//
		});
	}

}
