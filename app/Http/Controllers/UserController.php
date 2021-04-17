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
    		$data = array('email'=> $email, 'link' => URL::to('/api/createuser/'.$email));
   
	      	Mail::send('mail', $data, function($message) use ($email) {
	         	$message->to($email, 'Start Register')->subject('Create your username and password.');
	         	$message->from('email.stackdeveloper@gmail.com','Stack Dev');
	      	});
	      	echo "Invitation Email Sent.";
    		
    	}else{
    		echo "Please provide the user's email to send invitation.";
    	}
    }

}
