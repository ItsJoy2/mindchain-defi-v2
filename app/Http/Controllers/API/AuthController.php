<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Models\UserVerify;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    //  REGISTER
    public function register(Request $request)
    {
        DB::beginTransaction();

        try {

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
            $sponsor = null;

            if ($request->sponsor) {
                $sponsor = User::where('user_name', $request->sponsor)->first();

                if (!$sponsor) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Sponsor not found'
                    ], 404);
                }
            }

            // Generate referral code (A-Z + 0-9 only + unique)
            do {
                $referralCode = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);
            } while (User::where('referral_code', $referralCode)->exists());

            // Create user
            $user = User::create([
                'email' => $request->email,
                'user_name' => $request->user_name,
                'password' => $request->password,
                'sponsor_id' => $sponsor?->id,
                'referral_code' => $referralCode,
                'status' => 1
            ]);

            // Email verification token
            $token = Str::random(64);

            UserVerify::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            DB::commit();

            if ($user->email) {
                try {
                    Mail::send('emails.emailVerificationEmail', ['token' => $token], function ($message) use ($user) {
                        $message->to($user->email);
                        $message->subject('Verify Email');
                    });

                    Mail::to($user->email)->send(new WelcomeMail($user));

                } catch (\Exception $e) {
                    Log::error('Mail error: ' . $e->getMessage());
                }
            }

            // Token create
            $authToken = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Registered successfully. Please verify email.',
                'data' => [
                    'token' => $authToken,
                    'user' => [
                        'id' => $user->id,
                        'user_name' => $user->user_name,
                        'email' => $user->email,
                        'referral_code' => $user->referral_code
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //  LOGIN
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
            // if (is_null($user->email_verified_at)) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Email is not verified'
            //     ], 403);
            // }

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
    public function verifyEmail($token)
    {
        $verify = UserVerify::where('token', $token)->first();

        if (!$verify) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired token'
            ], 400);
        }

        $user = User::find($verify->user_id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Already verified check
        if ($user->email_verified_at) {
            return response()->json([
                'status' => true,
                'message' => 'Email already verified'
            ]);
        }

        $user->update([
            'email_verified_at' => now()
        ]);

        // delete token
        $verify->delete();

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully'
        ]);
    }
    public function resendVerification($id)
    {
        $user = User::find($id);
        $verify = UserVerify::where('user_id', $id)->first();

        if (!$user || !$verify) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        Mail::send('emails.emailVerificationEmail', ['token' => $verify->token], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Verify Email');
        });

        return response()->json([
            'status' => true,
            'message' => 'Verification email sent'
        ]);
    }


    /**
     * LOGOUT
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
