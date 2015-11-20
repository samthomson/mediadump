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
        // return a response with testedPath as what was sent if valid, or

        $oResponse = new \StdClass;
        $oResponse->bValidFolder = false;

        if(Request::has('path'))
        {
            $oResponse->testedPath = Request::get('path');

            $oResponse->bValidFolder = self::bTestDropboxFolderIsReal(Request::get('path'), Auth::user()->dropboxToken->accessToken);
        }else{
            $oResponse->sErrorMessage = "no folder entered";
        }

        return response()->json((array)$oResponse);
    }
    public static function bTestDropboxFolderIsReal($sFolderPath, $sOAuthToken)
    {
        // return true or false after talking to dropbox

        #echo "lets test: $sFolderPath";

        $client = new \GuzzleHttp\Client([
            // Base URI is used with relative requests
            // You can set any number of default request options.
            'timeout'  => 8.0,
            'verify' => false
        ]);

        try{

            
            $data = array("path" => $sFolderPath, "recursive" => false, "include_media_info" => false);                                                                    
            $data_string = json_encode($data);

            $aHeaders = [
                'Authorization' => 'Bearer '.$sOAuthToken,
                'Content-Type' => 'application/json'
            ];



            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL,"https://api.dropboxapi.com/2/files/list_folder");
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            /**/
            curl_setopt($curl, CURLOPT_POST, 1);                                                                
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string); 
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($curl, CURLOPT_HTTPHEADER, array( 
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
            );

            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer '.$sOAuthToken,
                'Content-Type: application/json'
            ));
            
            $result = curl_exec ($curl);

            if(curl_errno($curl))
            {
                #echo 'error:' . curl_error($curl);
            }


            curl_close ($curl);

            $oObj = json_decode($result);

            #print_r($oObj);

            if(isset($oObj->error_summary))
            {
                #echo "error";
                return false;
            }
            if(isset($oObj->entries))
            {
                #echo "yup";
                return true;
            }







        }catch(Guzzle\Http\Exception\RequestException  $e)
        {
            #print_r($e);
            return false;
        }
        return true;
    }
    public static function addDropboxFolder()
    {
        if(Request::has('path'))
        {
            $oResponse = new \StdClass;

            $bSuccess = self::addDropboxFolderToUser(Auth::user(), Request::get('path'), true);

            if(!$bSuccess)
            {
                // alreayd added!
                return response("folder already added", 428);
            }else{
                // successfully added, return updated list of folders
                $oResponse->dropboxFolders = Auth::user()->dropboxFolders;

                return response()->json((array)$oResponse);
            }
        }else{
            return response("no folder entered", 428);
        }
    }

    public static function addDropboxFolderToUser($oUser, $sFolderPath, $bRecursive = true)
    {
        if(count($oUser->dropboxFolders()->where('folder', $sFolderPath)->get()) > 0)
        {
            // alreayd added!
            return false;
        }else{

            $oDropboxFolder = new DropboxFolder;
            $oDropboxFolder->folder = $sFolderPath;
            $oDropboxFolder->recursive = $bRecursive;

            $oUser->dropboxFolders()->save($oDropboxFolder);

            return true;
        }
    }
}
