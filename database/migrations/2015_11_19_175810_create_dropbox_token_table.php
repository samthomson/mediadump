<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDropboxTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dropbox_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->index('user_id');
            $table->string('accessToken');
            $table->string('tokenType')->default("bearer");
            $table->string('uid');
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
        Schema::drop('dropbox_tokens');
    }
}
