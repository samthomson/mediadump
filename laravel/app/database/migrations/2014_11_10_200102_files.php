<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Files extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('files', function($table)
		{
			$table->increments('id');
			$table->string('path');
			$table->boolean('live')->default(false);
			$table->boolean('have_original')->default(true);

			$table->integer('medium_width')->default(0);
			$table->integer('medium_height')->default(0);

			$table->datetime('datetime');

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
		Schema::drop('files');
	}

}
