<?php

namespace App\Http\Controllers;

use App\Helper\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class UserController extends Controller
{
    //View Pages function
    function LoginPage():View{
        return view('pages.auth.login-page');
    }
    function RegistrationPage():View{
        return view('pages.auth.registration-page');
    }
    function SendOTPPage():View{
        return view('pages.auth.send-otp-page');
    }
    function VerifyOTPPage():View{
        return view('pages.auth.verify-otp-page');
    }
    function ResetPasswordPage():View{
        return view('pages.auth.reset-pass-page');
    }

    function ProfilePage():View
    {
        return view('pages.dashboard.profile-page');
    }

// Auth function
    function UserRegistration(Request $request)
    {
        try {
            User::create([
                'firstname' => $request->input('firstname'),
                'lastname' => $request->input('lastname'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'password' => $request->input('password'),
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'User Registration Successfully'
            ], status: 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User Registration failed'
            ], status: 200);
        }
    }

    function UserLogin(Request $request)
    {
        $count = User::where('email', $request->input('email'))
            ->where('password', $request->input('password'))
            ->select('id')->first();

        if ($count !== 0) {
            $token = JWTToken::CreateToken($request->input('email'),$count->id);
            return response()->json([
                'status' => 'success',
                'message' => 'User Login Successfully',
            ], status: 200)->cookie('token', $token, 60*24*30);

        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'User Login Failed'
            ], status: 200);
        }
    }

    function SendOTPCode(Request $request)
    {
        $email = $request->input('email');
        $otp = rand(1000, 9999);
        $count = User::where('email', '=', $email)->count();
        if ($count == 1) {
            //OTP send to mail
            Mail::to($email)->send(new OTPMail($otp));
            //OTP Database table update
            User::where('email', '=', $email)->update(['otp' => $otp]);
            return response()->json([
                'status' => 'success',
                'message' => 'OTP Send Successfully'
            ], status: 200);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'OTP send failed'
            ], status: 200);
        }
    }

    function VerifyOTP(Request $request)
    {
        $email = $request->input('email');
        $otp = $request->input('otp');
        $count = User::where('email', '=', $email)
            ->where('otp', '=', $otp)->count();

        if ($count > 0) {
            //OTP update Database
            User::where('email', '=', $email)->update(['otp' => '0']);
            //Pass Reset Token
            $token = JWTToken::CreateTokenForSetPassword($request->input('email'));
            return response()->json([
                'status' => 'success',
                'message' => 'OTP Verify Successfully',
            ], status: 200)->cookie('token', $token, 60*24*30);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized'
            ], status: 200);
        }
    }

    function ResetPassword(Request $request)
    {
        try {
            $email = $request->header('email');
            $password = $request->input('password');
            User::where('email', '=', $email)->update(['password' => $password]);
            return response()->json([
                'status' => 'success',
                'message' => 'Password Reset Successfully'
            ], status: 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Password Reset Failed'
            ], status: 200);
        }
    }

    function UserLogout()
    {
        return redirect('/userLogin')->cookie('token', '', -1);

    }

    function UserProfile(Request $request)
    {
        $email = $request->header('email');
        $user = User::where('email', '=', $email)->first();
        return response()->json([
            'status' => 'success',
            'message' => 'User Profile Successfully',
            'data' => $user
        ], status: 200);
    }

    function UpdateProfile(Request $request)
    {
        try {
            $email = $request->header('email');
            $firstname = $request->input('firstname');
            $lastname = $request->input('lastname');
            $mobile = $request->input('mobile');
            $password = $request->input('password');
            User::where('email', '=', $email)->update([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'mobile' => $mobile,
                'password' => $password,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Update Profile Successfully'
            ], status: 200);
        }catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Update Profile Failed'
            ], status: 200);
        }
    }


}

