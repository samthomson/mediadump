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
Route::get('/test', function()
{
	/*
	$oResults = QueueModel::where("date_from", "<", date('Y-m-d H:i:s'))
	->where("processor", "=", "delete")
	->take(1)
	->get();

	if(count($oResults) > 0){
		

		try
		{
			$oQi = $oResults[0];
			$oFile = FileModel::find($oQi->file_id);

			if(isset($oFile)){
				if(File::exists($oFile->path)){
					echo "file exists (".$oFile->path."), proceed to delete<br/>";
					//File::delete($oFile->path);
					unlink(realpath($oFile->path));
					if(File::exists($oFile->path)){
						echo "file found after delete: FAILURE<br/>";
					}else{
						echo "file not found after delete: SUCCESS<br/>";
					}
				}else{
					echo "file not found in first place<br/>";
				}
			}else{
				echo "couldn't orm file object from id<br/>";
			}

			
		}catch(Exception $er)
		{
			echo "<hr/>".$er;
		}
	}else{
		echo "no items to delete";
	}
	*/
});