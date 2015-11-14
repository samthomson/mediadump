<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', ['uses' => 'MediaDumpController@home']);

Route::get('/app/ping', ['uses' => 'MediaDumpController@ping']);


Route::post('/app/auth/register',  ['uses' => 'CustomAuthController@register']);
Route::post('/app/auth/login',  ['uses' => 'CustomAuthController@login']);
Route::post('/app/auth/logout',  ['uses' => 'CustomAuthController@logout']);


Route::get('/test/mail', function () {

	Mail::raw('Text to e-mail', function ($message) {
    	$message->to('samt@samt.st', 'sam')->subject('test subject');
	});


	Mail::send('emails.test', ['to' =>'sam', 'body' => 'test message'], function ($m) {
        $m->to('samt@samt.st', 'sam')->subject('test subject');
        $m->from('no-reply@mydomain.com', 'My Domain Sender');
    });
});
