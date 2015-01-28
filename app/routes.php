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
	return File::get(public_path() .DIRECTORY_SEPARATOR. 'frontend.html');
});


Route::get('auto/checkfiles', array('uses' => 'Auto@checkFiles'));
Route::get('auto/processqueue', array('uses' => 'Auto@processQueue'));


Route::get('/api/search', array('uses' => 'SearchController@search'));
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


Route::get('/test/index', array('uses' => 'SearchController@testIndex'));
Route::get('/test/search', array('uses' => 'SearchController@testSearch'));

Route::get('/test', function()
{
	$sKey = Helper::_AppProperty('imaggaKey');
	$sSecret = Helper::_AppProperty('imaggaSecret');
				
	echo "key, secret: $sKey, $sSecret<br/>";	
});
/*
Route::get('/test', function()
{

	
	// make search link from all unique tags
	$oaUniqueTags = TagModel::where("type", "=", "imagga")
	->groupBy("value")
	->orderBy("confidence", "desc")
	->get();

	foreach($oaUniqueTags as $oObj) {
		echo link_to('/#?query='.$oObj["value"], $oObj["value"]." (".$oObj["confidence"].")", array("target" => "_blank"), null)."<br/>";
	}
	
	// get all files
	$oFiles = FileModel::where("id", "<", 11138)->get();
	echo "found ".count($oFiles)." files<br/>";

	$saFiles = [];

	foreach ($oFiles as $file) {
		$saFiles[$file->id] = $file->path;
	}
	$iQueued = 0;
	foreach ($saFiles as $keyId => $keyPath) {
		$sExt = substr(strtolower($keyPath), strrpos(strtolower($keyPath), '.')+1);
			
		switch($sExt)
		{
			case "jpg":
			case "jpeg":
				// imagga processor afterwards
			try{
				$qiImagga = new QueueModel();
				$qiImagga->file_id = $keyId;
				$qiImagga->processor = "places";
				$qiImagga->date_from = date('Y-m-d H:i:s');
				$qiImagga->save();

				$iQueued++;
			}catch(Exception $e){}// dup index, already inserted
				break;
		}
	}
	echo "queued $iQueued files for places";
	
});*/