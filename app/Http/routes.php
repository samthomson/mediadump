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




Route::get('/test/dropbox/folder', function () {
	App\Http\Controllers\FileSourcesController::bTestDropboxFolderIsReal(
		Request::get('path'),
		'SS7HiGIa1ZoAAAAAAADMmgWZoeUARjlvFxA7bDNOC4RzJKVDC-xAwlsdfzm7yqP-'
		);



});

Route::get('/test/dropbox/files', function () {

	#App\Http\Controllers\FileSourcesController::getCompleteDropboxFolderContents('/photos - wilderness retreat', 'SS7HiGIa1ZoAAAAAAADMmgWZoeUARjlvFxA7bDNOC4RzJKVDC-xAwlsdfzm7yqP-');

	/*
	App\Http\Controllers\FileSourcesController::listDropboxFolderContents(
		'/pictures/PICTURES - SORTING FOR MEDIADUMP',
		'SS7HiGIa1ZoAAAAAAADMmgWZoeUARjlvFxA7bDNOC4RzJKVDC-xAwlsdfzm7yqP-'
		);
	*/


	App\Http\Controllers\FileSourcesController::getCompleteDropboxFolderContents('/pictures/PICTURES - SORTING FOR MEDIADUMP', 'SS7HiGIa1ZoAAAAAAADMmgWZoeUARjlvFxA7bDNOC4RzJKVDC-xAwlsdfzm7yqP-');

	#App\Http\Controllers\FileSourcesController::getCompleteDropboxFolderContents('/pictures/PICTURES - SORTING FOR MEDIADUMP/travel/bike-tours/Safari', 'SS7HiGIa1ZoAAAAAAADMmgWZoeUARjlvFxA7bDNOC4RzJKVDC-xAwlsdfzm7yqP-');


	
	

	
});
