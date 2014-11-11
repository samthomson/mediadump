<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return File::get(public_path() . '/angularfrontend.html');
});

Route::get('auto/checkfiles', array('uses' => 'Auto@checkFiles'));
Route::get('auto/processqueue', array('uses' => 'Auto@processQueue'));


Route::get('/api/search', array('uses' => 'SearchController@search'));
Route::get('/api/tree', array('uses' => 'SearchController@tree'));


Route::get('/test', function()
{
    $img = Image::make('C:\wamp\www\mediadump\laravel\public\media\africa\Atlas\DSC_7936.JPG')->resize(300, 200);

    return $img->response('jpg');
});





App::missing(function($exception)
{
    return Response::make('404', 404);
});