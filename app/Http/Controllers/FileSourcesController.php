<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Illuminate\Support\Facades\Request;

use App\User;
use App\Models\Settings;
use App\Models\DropboxFolder;
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
    public static function addDropboxFolder()
    {
        if(Request::has('path'))
        {
            $oResponse = new \StdClass;

            if(count(Auth::user()->dropboxFolders()->where('folder', Request::get('path'))->get()) > 0)
            {
                // alreayd added!
                return response("folder already added", 428);
            }else{

                $oDropboxFolder = new DropboxFolder;
                $oDropboxFolder->folder = Request::get('path');

                Auth::user()->dropboxFolders()->save($oDropboxFolder);


                $oResponse->dropboxFolders = Auth::user()->dropboxFolders;


                return response()->json((array)$oResponse);
                //return response("Ok", 200);
            }
        }else{
            return response("no folder entered", 428);
        }
    }
}
