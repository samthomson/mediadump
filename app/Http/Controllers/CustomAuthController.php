<?php


namespace App\Http\Controllers;

use App\Http\Middleware\Authenticate;
use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use Illuminate\Support\Facades\Request;
use App\Models\MediaDumpState;

use App\Http\Controllers\MediaDumpController;


use Auth;

class CustomAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */


    public function login()
    {
        $bResponse = null;
        $sResponseData = '';
        if(Auth::attempt(['email'=> Request::get('email'), 'password'=> Request::get('password')]))
            $bResponse = 200;
        else
        {
            $bResponse = 401;
            $sResponseData = '<div class="alert alert-danger"><strong>Login failed</strong> Enter the correct email and password or register.</div>';
        }

        return response($sResponseData, $bResponse);
    }
    public function logout()
    {
        return response("logged out", (Auth::logout() ? 401 : 200));
    }
    public function setup()
    {
        $iResponseCode = -1;
        $sResponseData = '';

        if(MediaDumpState::count() > 0)
        {
            // mediadump is already set up?
            $iResponseCode = 412;
            $sResponseData = 'Mediadump has already been set up ?!';
        }else{


            // md is clean, validate their data before continuing
            if(Request::has('email') && 
                Request::has('password') && 
                Request::has('password_confirmation') && 
                Request::has('name'))
            {
                // validate credentials, create user, login them in, return 200

                $validator = Validator::make(
                    Request::only(['email','password','password_confirmation', 'name']),
                    [
                        'email' => 'required|email|unique:users',
                        'password' => 'required|min:1|confirmed',
                        'name' => 'required'
                    ]
                );

                if ($validator->fails())
                {
                    $sResponseData = 'Make sure your email and password meet the following requirements:';

                    $iResponseCode = 412;
                    $sResponseData .= '<ul>';
                    foreach($validator->messages()->all('<li>:message</li>') as $message)
                    {
                        $sResponseData .= $message;
                    }
                    $sResponseData .= '</ul>';

                }else{

                    // succesful; create user, log them in, return 200
                    MediaDumpController::setupApplication(Request::get('name'), Request::get('email'), Request::get('password'));

                    Auth::attempt(['email' => $oUser->email, 'password' => $oUser->password], true);

                    $iResponseCode = 200;
                }
            }else{
                $iResponseCode = 412;
                $sResponseData = 'Enter an email and password to register';
            }   
        }
        return response($sResponseData, $iResponseCode);
    }

}