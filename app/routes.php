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


/*
*/

Route::get('/test/delete', array('uses' => 'ElasticSearchController@deleteIndex'));

Route::get('/elastic/re-index', array('uses' => 'ElasticSearchController@scheduleFullReindex'));


Route::get('/test', function()
{
	$client = new Elasticsearch\Client();

	$searchParams['index'] = 'mediadump_index';
/*
	$filter = array
			(
                "bool" => array
                (
                    "must" => array
                    (
                        "term" => array
                        (
                            "file_type" => 'mp4'
                        ),
                        "term" => array
                        (
                            "tags.value" => 'gopr0368'
                        )

                        
                    ),
                ),
            );*/
/*
	$filter = [
                "bool" => [
                    "must" => [
                        "term" => 
                        [
                            "hash" => "f6a3b3c868820ee5a1e071d9e70acff8"
                        ],
                        "term" => 
                        [
                            "file_type" => "mp4"
                        ]
                    ],
                ],
            ];
            */
	$filter = [
        "and" => [
            "filters" => [
                [
                	"term" => 
                    [
                        "tags.value" => '*'
                    ]
                ]
            ],
        ],
    ];


	$searchParams['body']['query']['filtered'] = array(
	    "filter" => $filter
	);
	



	//print_r(json_encode($searchParams));exit();
	
	$retDoc = $client->search($searchParams);

	//print_r($retDoc);
	return Response::json($retDoc);
});