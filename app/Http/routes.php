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


Route::post('/app/auth/setup',  ['uses' => 'CustomAuthController@setup']);
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



Route::get('/app/callback/{service}', function ($service) {

	switch ($service) {
		case 'dropbox':
			
			return File::get(public_path().'../../bower_components/ngDropbox/callback.html');
			break;
		
		default:
			echo "todo: 404?";
			break;
	}
});

Route::get('/app/connect/dropbox', function (Request $request) {
// get data from request
    $code = request('code');

    // get google service
    $dropboxService = \OAuth::consumer('DropBox');

    // check if code is valid

    // if code is provided get user data and sign in
    if ( ! is_null($code))
    {
        // This was a callback request from google, get the token
        $token = $dropboxService->requestAccessToken($code);

        $sAccessToken = $token->getAccessToken();

        if(Auth::check())
        {
        	$oDropboxToken = new App\Models\DropboxToken;
        	$oDropboxToken->accessToken = $sAccessToken;

        	Auth::user()->dropboxToken()->save($oDropboxToken);
        	return redirect(url().'/#/admin/filesources');
        }
    }
    // if not ask for permission first
    else
    {
        // get googleService authorization
        $url = $dropboxService->getAuthorizationUri();

        // return to google login url
        return redirect((string)$url);
    }
});


Route::post('/app/filesources/dropbox/test', ['uses' => 'FileSourcesController@testDropboxFolder']);

Route::post('/app/filesources/dropbox/add', ['uses' => 'FileSourcesController@addDropboxFolder']);




Route::get('/test/dropbox', function (Request $request) {

	
    // get data from request
    $code = request('code');

    // get google service
    $dropboxService = \OAuth::consumer('DropBox');

    // check if code is valid

    // if code is provided get user data and sign in
    if ( ! is_null($code))
    {
        // This was a callback request from google, get the token
        $token = $dropboxService->requestAccessToken($code);

        // Send a request with it
        //$result = json_decode($dropboxService->request('/account/info'), true);
        /*
        array:11 [▼
		"referral_link" => "https://db.tt/Dg8P2FY8"
		"display_name" => "samt t"
		"uid" => 137223662
		"locale" => "en"
		"email_verified" => true
		"team" => null
		"quota_info" => array:4 [▼
		"datastores" => 0
		"shared" => 145644415
		"quota" => 1102732853248.0
		"normal" => 107675663965.0
		]
		"is_paired" => false
		"country" => "MA"
		"name_details" => array:3 [▼
		"familiar_name" => "samt"
		"surname" => "t"
		"given_name" => "samt"
		]
		"email" => "samt@samt.st"
		]
		*/
        //$result = json_decode($dropboxService->request('/oauth2/token_from_oauth1'), true);
        /*
		*/

        echo 'Your unique dropbox stuff:<br/>';
        //print_r($result);

        //Var_dump
        //display whole array.
        #dd($result);
        dd($token);
    }
    // if not ask for permission first
    else
    {
        // get googleService authorization
        $url = $dropboxService->getAuthorizationUri();

        // return to google login url
        return redirect((string)$url);
    }
});

Route::get('/test/dropbox/files', function (Request $request) {

	
});
