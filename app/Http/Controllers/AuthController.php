<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\User;
use URL;
use Mail;
use Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
// import the storage facade
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public $loginAfterSignUp = true;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get the bearer token from the request headers.
     *
     * @return string|null
    */
    public function bearerToken($request)
    {
        $header = $request->header('Authorization', '');
        if($header){
            if (Str::startsWith($header, 'Bearer ')) {
                $token = Str::substr($header, 7);
                
                // Verify the token with the user.
                $check = User::where('api_token', $token)->first();
                if($check){
                    return ['msg' => 'User is authorized.', 'status'=>100];
                }else{
                    return ['msg' => 'Unauthorized user.', 'status'=>101];
                }
            }
        } else{
            return ['msg' => 'Authentication token is missing.', 'status'=>101];
        }
    }

    public function register(Request $request)
    {
        
        $pin = rand ( 100000 , 999999 );

        $user   = User::create([
            'name'      => $request->username,
            'username'  => $request->username,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'pin'       => $pin
        ]);

        // Save pin into DB
        $this->sendOTPemail($user, $pin);
        return response()->json(['msg' => 'Pin code has been sent in the email. Please use that for the completion of the registration', 'status'=>100]);

    }

    public function sendOTPemail($user, $pin){
        $email = $user->email;        
        $data  = array('name' => $user->name, 'pin' => $pin, 'link' => URL::to('/api/verifypin/'.$user->id));

        // Send email
        Mail::send('pin', $data, function($message) use ($email) {
            $message->to($email, 'Register Process')->subject('Pin code for the register.');
            $message->from('email.stackdeveloper@gmail.com','Stack Dev');
        });
        return true;
    }

    // Verify pin and complete registration.
    public function verifyPin(Request $request, $id){
        if($id){
            $pin = $request->input('pin');
            $token  = Str::random(60);
            $hashed = hash('sha256', $token);
            $user = User::find($id);
            if($user){
                if($user->pin == $pin){
                    $user->status = 1;
                    $user->api_token = $hashed;
                    $user->save();
                    return $this->sendToken($hashed);
                }else{
                    return response()->json(['msg' => 'Please provide the valid pin to verify.', 'status'=>101]);
                }
            }
        }else{
            return response()->json(['msg' => 'Url is not valid.', 'status'=>101]);
        }
    }

    public function login(Request $request)
    {
      // Get the currently authenticated user...

        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
           $user = Auth::user();
           // echo "<pre>";print_r($user);die('success');
           $data['token']  = $this->update($user->id);
           $data['msg']    = 'Successfully login.';
           $data['status'] = 100;
           return response()->json(['data' => $data]);
        }
        else{
           return response()->json(['error'=>'Unauthorised', 'status'=>101, 'msg'=> "Email and password don't match."]);
        }      
    }

    /**
     * Update the authenticated user's API token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function update($user_id)
    {
        $token = Str::random(60);
        $hashed = hash('sha256', $token);
        $user = User::find($user_id);
        $user->api_token = $hashed;
        $user->save();
        return $hashed;
    }

    public function updateProfile(Request $request, $id){
        //validator place
        $token = $this->bearerToken($request);
        
        if(empty($token)){
            return response()->json($token);
        } else{
            if($token['status'] == 100){
                $users = User::find($id);
                $users->name   = $request->name;
                // $this->validate($request, [
                //     'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                // ]);
                $image = $request->file('avatar');
                if($image){
                    $imagename = time().'.'.$image->extension();
                    $destinationPath = storage_path('/app/public/avatar');      
                    Image::make($image->getRealPath())->resize(250, 250)->save($destinationPath . '/' . $imagename, 100);
                    //$img = Image::make($image->path());
                    // $img->resize(250, 250, function ($constraint) {
                    //     $constraint->aspectRatio();
                    // })->save($destinationPath.'/'.$imagename);
                    $users->avatar = 'avatar/'.$imagename;
                }
                
                $users->save();
                $data['name'] = $users->name;
                if($image){
                    $data['avatar'] = Storage::url($users->avatar);
                    $data['profile_image'] = URL::to('/storage/app/public/'.$users->avatar);
                }
                // $data[] = [
                //     'name'=>,
                //     'avatar'=>Storage::url($users->avatar),
                //     'profile_image'=> URL::to('/storage/app/public/'.$users->avatar)
                // ];
                return response()->json(['data'=>$data, 'msg'=>"User's profile has been updated.", 'status'=>100]);
            }else{
                return response()->json($token);
            }           
        }
    }


    public function getAuthUser(Request $request)
    {
        return response()->json(auth()->user());
    }
    public function logout()
    {
        auth()->logout();
        return response()->json(['message'=>'Successfully logged out']);
    }
    protected function sendToken($token)
    {
      return response()->json([
        'access_token' => $token,
        'token_type' => 'bearer',
        // 'expires_in' => auth()->factory()->getTTL() * 60
      ]);
    }
}
// 