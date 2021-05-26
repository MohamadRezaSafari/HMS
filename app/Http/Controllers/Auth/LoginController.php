<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use App\Http\Requests;
use GuzzleHttp\Client;

class LoginController extends Controller
{
    private $url = "/";

    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    //
    // protected $maxLoginAttempts = 1;
    // protected $lockoutTime = 600;
    //
    //

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = "/d56d985300d4b52eb6e189be006f44f8d23c5ec9";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->validateSystem();
        $this->middleware('guest', ['except' => 'logout']);
    }


    /*protected function validateSystem()
    {
//        $request = \Illuminate\Http\Request::create(
//            'http://expire.web-artisans.ir/validateSystem/v1/checkSum',
//            'POST',
//            [
//                'url' => Crypt::encrypt(\Request::root())
//            ]
//        );

        $client = new Client();
        $res = $client->request('POST', 'http://expire.web-artisans.ir/validateSystem/v1/checkSum', [
            'form_params' => [
                'url' => base64_encode(\Request::root())
            ]
        ]);
        echo $res->getBody();
        die();
    }*/

    /*public function login(Request $request)
    {
        /*if (\Auth::attempt(['email' => $request->get('email'), 'password' => $request->get('password')])) {

            if(\Auth::user()->roles[0]['roleName'] == "hospitalAdmin" || \Auth::user()->roles[0]['roleName'] == "hospital"){

                return redirect('roleHospital');

            }

            if(\Auth::user()->roles[0]['roleName'] == "admin"){

                return redirect('dashboard');

            }

            if(\Auth::user()->roles[0]['roleName'] == "doctor"){

                return redirect('roleDoctor');

            }

            if(\Auth::user()->roles[0]['roleName'] == "owner"){

                return redirect('city');

            }

            else{

                return redirect('login');

            }
        }
    }*/
}
