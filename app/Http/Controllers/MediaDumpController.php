<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\User;


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

    	if(User::count() > 0)
    	{
    		// there are registered users
    		$oReturn->md_state = "setup";
    	}else{
    		// no users, empty mediadump?
    		$oReturn->md_state = "clean";
    	}

    	return response()->json($oReturn);
    }
}
