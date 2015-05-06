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

Route::get('/elastic/create', array('uses' => 'ElasticSearchController@createIndex'));

Route::get('/elastic/re-index', array('uses' => 'ElasticSearchController@scheduleFullReindex'));


Route::get('/test', function()
{
	
	$client = new Elasticsearch\Client();

	$searchParams['index'] = 'mediadump_index';

/*
	$filter = [
		"bool" => [
			"must" => [
				"range" => [
					"longitude" => ["lt" => 0, "gt" => -30],
					"latitude" => ["gt" => 50]
				]
	        ]
	    ]
    ];
    */
    $ands = [];
    /**/
    array_push($ands, array("term" => array("tags.value" => "test")));

	$filter = [
		"and" => $ands
    ];


    //$searchParams['body']['query']['filtered']['query']['match_all'] = new \stdClass();
	$searchParams['body']['query']['filtered'] = array(
	    "filter" => $filter
	);
	

	//print_r(json_encode($searchParams));exit();
	
	$retDoc = $client->search($searchParams);

	//print_r($retDoc);
	return Response::json($retDoc);
/**/
		
});

Route::get('/test/index', function()
{
	$client = new Elasticsearch\Client();
	$indexParams['index']  = 'mediadump_index';

	// Example Index Mapping
	$myTypeMapping = array(
	    '_source' => array(
	        'enabled' => true
	    ),
	    'properties' => array(
	        'fieldName' => array("type" => "geo_point"),
	        'tags.value' => array("type" => "string", "index" => "not_analyzed")
	    )
	);
	$indexParams['body']['mappings']['file'] = $myTypeMapping;

	// Create the index
	$client->indices()->create($indexParams);
		
});