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
Route::get('/test/index', array('uses' => 'SearchController@queueIndex'));
Route::get('/test/search', array('uses' => 'SearchController@testSearch'));
Route::get('/test/index', array('uses' => 'SearchController@queueIndex'));

Route::get('/test/create-index', array('uses' => 'ElasticSearchController@createIndex'));

/*
*/

Route::get('/test/delete', array('uses' => 'ElasticSearchController@deleteIndex'));

Route::get('/elastic/re-index', array('uses' => 'ElasticSearchController@scheduleFullReindex'));


Route::get('/test', function()
{
	/*
	$iaFiles = [1,3];

	foreach($iaFiles as $iFileId)
	{
		$oFile = FileModel::find($iFileId);

		$oFFProbe = FFMpeg\FFProbe::create();

		$mVideoProbe = $oFFProbe
		  ->streams($oFile->path)
		  ->videos()
		  ->first();

		$mDuration = $mVideoProbe->get('duration');
		$mTags = $mVideoProbe->get('tags');

		print_r($mTags);		
		echo "<br/>";
	}
	*/

	
	//var_dump($mDuration);
	//var_dump($mTags);
	

	VideoProcessor::process(2, "webm");

	/*
	$oFile = FileModel::find(1);

	$ffmpeg = FFMpeg\FFMpeg::create(array(
		'ffmpeg.binaries'  => 'C:/ffmpeg/bin/ffmpeg.exe',
		'ffprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe'
		)
	);

	$video = $ffmpeg->open($oFile->path);
	if(!file_exists($oFile->path))
	{
		echo "couldnt find";exit();
	}

	$sIn = $oFile->path;
	$sOut = Helper::thumbPath("test").$oFile->id.'.jpg';

	$sCommand = "ffmpeg -i $sIn -vframes 1 -filter:v scale=\"-1:300\" $sOut";
	exec($sCommand);
	*/




	/*
	$ffmpeg = FFMpeg\FFMpeg::create(array(
		'ffmpeg.binaries'  => 'C:/ffmpeg/bin/ffmpeg.exe',
		'ffprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe'
		)
	);
	$oFile = FileModel::find(1);
	$video = $ffmpeg->open($oFile->path);

	$format = new FFMpeg\Format\Video\WebM();

	$format->on('progress', function ($video, $format, $percentage) {
		if($percentage > 99){
	    	exit();
		}
	    echo "$percentage % transcoded<br/>";
	});

	$video->save($format, Helper::thumbPath("test").$oFile->id.'.webm');
	*/
});