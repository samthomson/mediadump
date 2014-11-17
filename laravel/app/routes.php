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

Route::get('/api/stats/auto/overview', array('uses' => 'StatsController@autoOverview'));
Route::get('/api/stats/auto/events', array('uses' => 'StatsController@autoEvents'));

Route::get('/admin/events', function()
{
	return View::make('admin.events');
});
Route::get('/admin', function()
{
  return View::make('admin.overview');
});

Route::post('/admin/events', array('uses' => 'StatsController@makeAutoEvents'));


App::missing(function($exception)
{
    return Response::make('404', 404);
});