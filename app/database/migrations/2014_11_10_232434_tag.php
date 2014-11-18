<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Tag extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tags', function($table)
		{
			$table->increments('id');
			$table->integer('file_id');
			$table->string('type');
			$table->string('value');

			$table->index(array('file_id', 'type', 'value'));
			$table->unique(array('file_id', 'type', 'value'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('tags');
	}

}