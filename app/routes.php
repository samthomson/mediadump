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
	return View::make("pages/frontend");
});


Route::get('/auto/checkfiles', array('uses' => 'AutoController@checkFiles'));
Route::get('/auto/processqueue', array('uses' => 'AutoController@processQueue'));

Route::get('/api/search', array('uses' => 'SearchController@elasticSearch'));
Route::get('/api/suggest', array('uses' => 'SearchController@suggest'));
Route::get('/api/dbsuggest', array('uses' => 'SearchController@dbSuggest'));
Route::get('/api/suggest/stats', array('uses' => 'SearchController@suggestStats'));
Route::get('/api/tree', array('uses' => 'SearchController@tree'));
Route::get('/api/cache/rebuild', array('uses' => 'CacheController@rebuild'));

Route::get('/api/stats/auto/overview', array('uses' => 'StatsController@autoOverview'));

Route::get('/api/stats/auto/events', array('uses' => 'StatsController@autoEvents'));


Route::get('/view/filedata', array('uses' => 'BaseController@fileData'));

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




/**/
Route::get('/test/delete', array('uses' => 'ElasticSearchController@deleteIndex'));

Route::get('/elastic/create', array('uses' => 'ElasticSearchController@createIndex'));

Route::get('/elastic/re-index', array('uses' => 'ElasticSearchController@scheduleFullReindex'));


Route::get('/test', function()
{

	//echo imagecreatefromjpeg('C:\wamp\www\mediadump\public\media\test-hungary-2015\DSC09069.JPG');

	$img = Image::make('C:\wamp\www\mediadump\public\media\test-hungary-2015\DSC09069.JPG')->orientate();

	print_r($img);
	
	/*
	$oFile = FileModel::find(1);

	$data = @imagecreatefromjpeg($oFile->path);

	//$data = Image::make($oFile->path)->exif();

	echo (Helper::bImageCorrupt($oFile->path) ? "corrupt" : "okay");

	print_r(exif_read_data($oFile->path));
	
	*/
	/*

	if(!getimagesize($oFile->path))
	{
		echo "corrupt";
	}else{
		echo "ok";
	}
	*/
});
Route::get('/empty', function()
{
	return Response::make("ok", 200);
});


Route::get('/elastic/reset', function()
{
	ElasticSearchController::deleteIndex();
	ElasticSearchController::createIndex();
	ElasticSearchController::scheduleFullReindex();

	return Response::make("<br/><br/>- ok", 200);
});