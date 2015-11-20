<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Support\Facades\Request;

use App\User;
use App\Models\Settings;
use Auth;

class FileSourcesController extends Controller
{

    public static function testDropboxFolder()
    {
        if(Request::has('path'))
        {
            $oResponse = new \StdClass;
            $oResponse->testedPath = Request::get('path');

            return response()->json((array)$oResponse);
        }else{
            return response("no folder entered", 428);
        }
    }
}
