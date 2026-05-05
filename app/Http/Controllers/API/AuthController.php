<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Models\UserVerify;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ===================== REGISTER =====================
    public function register(Request $request)
    {
        try {

            // Validation
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:users,email',
                'user_name' => 'required|string|max:255|unique:users,user_name|regex:/^\S*$/u',
                'password' => 'required|min:8|confirmed',
                'sponsor' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Sponsor check
            if ($request->sponsor) {
                $sponsor = User::where('user_name', $request->sponsor)->first();

                if (!$sponsor) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Sponsor user not found'
                    ], 404);
                }
            } else {
                $sponsor = User::find(11223344);
            }

            // Random key
            $random = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 7);

            // Create user
            $user = User::create([
                'email' => $request->email,
                'user_name' => $request->user_name,
                'password' => Hash::make($request->password),
                'sponsor' => $sponsor->id,
                'key_id' => $random,
                'is_email_verified' => 0
            ]);

            // Email verify token
            $token = Str::random(64);

            UserVerify::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            // Mail send 
            try {
                Mail::send('emails.emailVerificationEmail', ['token' => $token], function($message) use($user){
                    $message->to($user->email);
                    $message->subject('Email Verification Mail');
                });

                Mail::to($user->email)->send(new WelcomeMail($user));
            } catch (\Exception $e) {
                // ignore mail error
            }

            // Token create
            $authToken = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Registration successful. Please verify your email.',
                'data' => [
                    'token' => $authToken,
                    'user' => [
                        'id' => $user->id,
                        'user_name' => $user->user_name,
                        'email' => $user->email
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    // ===================== LOGIN =====================
    public function login(Request $request)
    {
        try {

            // Validation
            $validator = Validator::make($request->all(), [
                'user_name' => 'required|string',
                'password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // User check
            $user = User::where('user_name', $request->user_name)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'No user found with this username'
                ], 404);
            }

            // Email verify check
            if ($user->is_email_verified == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email is not verified'
                ], 403);
            }

            // Password check
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Username or password is incorrect'
                ], 401);
            }

            // Token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'user_name' => $user->user_name,
                        'email' => $user->email
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * EMAIL VERIFICATION RESEND
     */
    public function verification($id)
    {
        $token = UserVerify::where('user_id', $id)->first();
        $user = User::find($id);

        if (!$user || !$token) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        Mail::send('emails.emailVerificationEmail', ['token' => $token->token], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Email Verification Mail');
        });

        return response()->json([
            'status' => true,
            'message' => 'Verification email sent successfully'
        ]);
    }



    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
