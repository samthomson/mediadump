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
    	return view('app.home');
    	
    	if(User::count() > 0)
    	{
    		// there are registered users
    		echo 'login?';
    	}else{
    		// no users, empty mediadump?
    		echo 'register';
    	}
    }
}
