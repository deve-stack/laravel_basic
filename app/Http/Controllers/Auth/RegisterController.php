<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;


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
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    public function create(Request $request,  $email)
    {
        // return User::create([
        //     'name' => $data['name'],
        //     'email' => $data['email'],
        //     'password' => Hash::make($data['password']),
        // ]);
        $username = $request->input('username');
        $password = $request->input('password');
        $check_user = User::where('username', $username)->first();
        if($check_user){
            // die('success32');
            echo "Username already exists.";
            // echo "22<pre>";print_r($check_user);die;
        }else{
            // die('success22');
            $request->validate([
                'username' => 'required',
                // 'email' => 'required',
                'password' => 'required'
            ]);

        // die('sukhll');
            $user = User::create([
                'name' => trim($username),
                'username' => trim($username),
                'email' => strtolower($email),
                'password' => bcrypt($password),
                // 'password' => Hash::make($password),
                // 'api_token' => Str::random(80),
            ]);

            // echo "<pre>";print_r($user);die;
            $token = auth()->login($user);
            
            echo 'token--->>'.$token;die;
            return $this->getToken($token);
        }
    }

      // Api for login
    public function login(Request $request)
    {
         $credentials = $request->only(['email', 'password']);

      if (!$token = auth()->attempt($credentials)) {
        return response()->json(['error' => 'Unauthorized'], 401);
      }

      return $this->getToken($token);
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
    }

    protected function getToken($token)
    {
      return response()->json([
        'token' => $token,
        'token_type' => 'bearer',
        // 'expires_in' => auth('api')->factory()->getTTL() * 60
      ]);
    }
}
