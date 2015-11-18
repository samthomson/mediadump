<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\User;
use App\Models\MediaDumpState;
use Auth;

class MediaDumpController extends Controller
{

    public static function home()
    {
    	// serve angular app!
    	return view('app.home');
    }

    public static function ping()
    {
    	// client is polling md backend
    	// maybe there's a ui to be made, maybe md is blank and needs set up, serve them a status code with corresponding data, the client will then choose what to do with it

    	$oReturn = new \StdClass;

    	if(MediaDumpState::count() > 0)
    	{
    		// there are registered users
    		$oReturn->md_state = "setup";
    	}else{
    		// no users, empty mediadump?
    		$oReturn->md_state = "empty";
    	}

        $oReturn->bLoggedIn = Auth::check();

    	return response()->json((array)$oReturn);
    }

    public static function setupApplication($sName, $sEmail, $sPassword, $bPublic = true)
    {
        // creates md state and associated user

        // create master md state
        $oMDState = new MediaDumpState;
        $oMDState->public = $bPublic;

        $oMDState->save();

        $oUser = new User;
        $oUser->name = $sName;
        $oUser->email = $sEmail;
        $oUser->password = \Hash::make($sPassword);
        $oUser->admin = 1;
        $oUser->save();

        // save user as md state relation
        $oMDState->ownerUser()->save($oUser);
    }
}
