<?php

namespace App\Http\Controllers;

use App\Events\UserEmailverified;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\register;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Verified;

class AuthController extends Controller
{
    public function registerview(){
        $roles = ['1' =>'admin', '2' => 'manager', '3' => 'user'];
        return view('auth.register', compact('roles'));
    }
    public function register(register $request){
        $validated = $request->validated();
        try{
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'gender' => $validated['gender'],
                'role' => $validated['role'],
                'password' => Hash::make($validated['password']),
            ]);
            return $this->responceReturn($request,'success', 'Your email has been register with us',false, 200);
           // return back()->with('success', 'Your email has been register with us');
        }
        catch (\Exception $e){
           return $this->responceReturn($request,'fail', 'getting error in registeration'.$e->getMessage(),[['getting'.$e->getMessage()]],500 );
            //return back()->with('fail', "getting error in registeration".$e->getMessage());            
        }        
    }
    public function loginview(){    
        return view('auth.login');
    }
    public function login(LoginRequest $request){
        $validated = $request->validated();
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];
        if(Auth::attempt($credentials)){
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }
        return back()->withErrors([
            'email' => 'The provided credentials are not matched with our records',
        ])->onlyInput('email');

    }
    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
    public function piramid(){
        $row = 10;
        $min = $row;
        $l = '';
        for($i=0; $i<$row; $i++){          
            for($j=0; $j<$row; $j++){
                if($j == $min){
                    echo $l .= "* ";
                }else{                   
                    echo " ";
                }
            }
            $min = $min-1;
            echo "<br>";
        } 
    }
    public function piramid1(){
        $row = 10;
        $max = $row;
        $l = '';
        for($i=0; $i<$row; $i++){
            //echo $i;
            for($j=0; $j<=$max; $j++){
                if($j == $max){
                   echo  $l .= " * ";
                }
                else{  
                   
                    echo   " 1 "; 
                }
            }
            $max = $max-1;
            echo "<br>";
        } 
    }
    public function responceReturn(Request $request,$status, $message, $errors,$statuscode){
        if ($request->is('api/*')) {
            return response()->json([
                'status' => $status,
                'errors' => $errors,
                'message' => $message,
         ], $statuscode);
        } else {
              return back()->with($status, $message);
        }
    }
    public function verificationResend($id){
        $user = User::find($id);
        $user->sendEmailVerificationNotification();
        return redirect()->route('verification.notice')->with('status', 'A verification link has been sent to your email address.');
    }
    public function verificationNotice(){
        //it simply return a view to notified user that link has been sent to your email
        return view('verification.notice');
    }
    public function verificationVerify(Request $request){
        try {        
            $user = User::findOrfail($request->id);       
            if ($user->hasVerifiedEmail()) {
                    return redirect()->intended('dashboard');
            }          
            //get the user based on the link from the request and marked user as varified and its returning true raised and event that user has been varified 
            if ($user->markEmailAsVerified()) {
                event(new UserEmailverified($user));
            }
            return redirect()->intended('dashboard');
        } catch (\Exception $e) {
            return redirect()->intended('email/notice')->with('fail', 'Requested user are not register with us'. $e);        //throw $th;
        }
    }
}
