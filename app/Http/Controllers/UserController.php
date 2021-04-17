<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use URL;
use App\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    //Send email notification
    public function sendInvitation(Request $request){
    	
    	// die('success');
    	if($request->input('email')){
    		$email = $request->input('email');
    		$data = array('email'=> $email, 'link' => URL::to('/api/create_user/'.$email));
   
	      	Mail::send('mail', $data, function($message) use ($email) {
	         	$message->to($email, 'Start Register')->subject('Create your username and password.');
	         	$message->from('email.stackdeveloper@gmail.com','Stack Dev');
	      	});
	      	echo "Invitation Email Sent.";
    		
    	}else{
    		echo "Please provide the user's email to send invitation.";
    	}
    }

    // Api to create user
    public function createUser(Request $request, $email){
    	// echo "<pre>";print_R($_POST);die;
    	// echo $email;die;
    	// echo "auth<pre>";print_R(auth());die;
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
    		// die('11');
    	}
    }


    protected function getToken($token)
    {
      return response()->json([
        'token' => $token,
        'token_type' => 'bearer',
        // 'expires_in' => auth('api')->factory()->getTTL() * 60
      ]);
    }

    // Api for login
    public function userlogin($user)
    {
        // $request->validate([
        //     'username' => 'required',
        //     'password' => 'required'
        // ]);

        // $credentials = $request->except(['_token']);

        $user = User::where('username',$user->username)->first();
        $credentials = $request->only(['email', 'password']);

        if (!$token = auth()->attempt($credentials)) {
        	echo response()->json(['error' => 'Unauthorized'], 401);
        }

        echo $this->getToken($token);
        // if (auth()->attempt($credentials)) {
        // 	echo "Login successful";

        // }else{
        // 	echo "Invalid credentails";
        // }
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

        // $credentials = $request->except(['_token']);

        // $user = User::where('username',$request->username)->first();
        // $credentials = $request->only(['email', 'password']);

        // if (!$token = auth()->attempt($credentials)) {
        // 	echo response()->json(['error' => 'Unauthorized'], 401);
        // }

        // echo $this->getToken($token);
        // if (auth()->attempt($credentials)) {
        // 	echo "Login successful";

        // }else{
        // 	echo "Invalid credentails";
        // }
    }
}
