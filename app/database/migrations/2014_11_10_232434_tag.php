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

			$table->index('file_id', "tags_fileid");
			$table->index('type', "tags_type");
			$table->index('value', "tags_value");
			
			$table->tinyInteger('confidence')->default(50); 

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