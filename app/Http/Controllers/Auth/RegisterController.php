<?php

namespace App\Http\Controllers\Auth;

use App\HCL_User;
use App\HealthcareCenterList;
use App\Role;
use App\Role_User;
use App\User;
use Carbon\Carbon;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Doctor;
use App\Doctor_User;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth', 'owner']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        // $post->tags()->attach($request->get('tag'));
    }


    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showRegistrationForm ()
    {
        $role = Role::pluck('roleName', 'id');
        $hospital = HealthcareCenterList::where('healthcareCenter_id', '=', 1)
          ->pluck('healthcareCenterListName', 'id');
        $_user = Doctor_User::pluck('doctor_id');
        $doctor = Doctor::whereNotIn('id', $_user)
              ->pluck('doctorLastName', 'id');
        return view('auth.register', compact('role', 'hospital', 'doctor'));
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function register(Request $request)
    {
        $user = $request->all();

        User::insert([
            'name' => $user['name'],
            'email' => $user['email'],
            'password' => bcrypt($user['password']),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $id = User::latest('id')->take(1)->value('id');

        Role_User::insert([
            'role_id' => $user['role'],
            'user_id' => $id
        ]);

        if($request->get('hospital')){
            HCL_User::insert([
                'hcl_id' => $request->get('hospital'),
                'user_id' => $id
            ]);
        }

        if($request->get('doctor')){
            Doctor_User::insert([
                'doctor_id' => $request->get('doctor'),
                'user_id' => $id
            ]);
        }

        return redirect('/');
    }
}
