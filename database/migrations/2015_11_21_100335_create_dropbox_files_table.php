<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDropboxFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dropbox_files', function (Blueprint $table) {
            $table->increments('id');

            // relations
            $table->integer('dropbox_folder_id'); // this folder is then tied to the user

            // dropbox properties
            $table->string('dropbox_id');
            $table->string('dropbox_path');
            $table->string('dropbox_name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dropbox_files');
    }
}
