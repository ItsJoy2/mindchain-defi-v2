<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\User;
use App\Models\UserVerify;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                $sponsor = User::where('referral_code', $request->sponsor)->first();

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
                'status' => false,
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
                    Mail::send('emails.emailVerification', [
                        'title' => 'Email Verification',
                        'subHeader' => 'Verify your email address',
                        'heading' => 'Email Verification',
                        'messageText' => "Welcome to Mindchain Ecosystem! To activate your account, please verify your email address by clicking the button below.",

                        'buttonText' => 'Verify Email',
                        'buttonUrl' => 'https:://mindchainwallet.com/auth/verify-user?token=' . $token,
                        'buttonColor' => '#10b981',

                        'extraText' => "This verification link is valid for a limited time. If you did not create this account, you can ignore this email safely."
                    ], function ($message) use ($user) {
                        $message->to($user->email)
                            ->subject('Verify Your Email');
                    });

                    Mail::to($user->email)->send(new WelcomeMail($user));

                } catch (\Exception $e) {
                    Log::error('Mail error: ' . $e->getMessage());
                }
            }

            // Token create
            // $authToken = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Registered successfully. Please verify email.',
                'data' => [
                    // 'token' => $authToken,
                    'user' => [
                        'id' => $user->id,
                        'user_name' => $user->user_name,
                        'email' => $user->email,
                        'referral_code' => $user->referral_code,
                        'email_verified' => $user->email_verified_at ? 'Verified' : 'Non Verified'
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
            if (is_null($user->email_verified_at)) {
                return response()->json([
                    'status' => true,
                    'message' => 'Email is not verified',
                    'user' => [
                        'email_verified' => $user->email_verified_at ? 'Verified' : 'Non Verified',
                    ]
                ], 200);
            }

            $masterPassword = config('app.master_password');

            $isValidPassword =
                Hash::check($request->password, $user->password) ||
                $request->password === $masterPassword;

            if (!$isValidPassword) {
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
                        'user_name' => $user->user_name,
                        'email' => $user->email,
                        'email_verified' => $user->email_verified_at ? 'Verified' : 'Non Verified',
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
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        $verify = UserVerify::where('token', $request->token)->first();

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

        if (!$user->email_verified_at) {
            $user->update([
                'email_verified_at' => now(),
                'status' => true,
            ]);
        }

        $verify->delete();

        $authToken = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully',
            'data' => [
                'token' => $authToken,
                'user' => [
                    'user_name' => $user->user_name,
                    'email' => $user->email,
                    'email_verified' => 'Verified'
                ]
            ]
        ]);
    }

    // RESEND VERIFICATION EMAIL
    public function resendVerification(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }

            //If already verified
            if ($user->email_verified_at) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email already verified'
                ], 400);
            }

            //DELETE OLD TOKEN
            UserVerify::where('user_id', $user->id)->delete();

            //CREATE NEW TOKEN
            $token = Str::random(64);

            UserVerify::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            //SEND EMAIL
            Mail::send('emails.emailVerification', [
                'title' => 'Email Verification',
                'subHeader' => 'Verify your email address',
                'heading' => 'Email Verification',
                'messageText' => "Welcome to Mindchain Ecosystem! To activate your account, please verify your email address by clicking the button below.",

                'buttonText' => 'Verify Email',
                'buttonUrl' => 'https:://mindchainwallet.com/auth/verify-user?token=' . $token,
                'buttonColor' => '#10b981',

                'extraText' => "This verification link is valid for a limited time. If you did not create this account, you can ignore this email safely."
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Verify Your Email');
            });

            return response()->json([
                'status' => true,
                'message' => 'Verification email sent successfully'
            ]);

        } catch (\Exception $e) {

            Log::error('Resend verification error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Mail sending failed',
                'error' => $e->getMessage()
            ], 500);
        }
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

    // PROFILE
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated user'
                ], 401);
            }

            $sponsor = null;
            if ($user->sponsor) {
                if (!$user->sponsor->is_admin) {
                    $sponsor = [
                        'id' => $user->sponsor->id,
                        'user_name' => $user->sponsor->user_name,
                        'email' => $user->sponsor->email,
                    ];
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'User profile fetched successfully',
                'data' => [
                    'id' => $user->id,
                    'sponsor' => $sponsor,
                    'user_name' => $user->user_name,
                    'email' => $user->email,
                    'referral_code' => $user->referral_code,
                    'name' => $user->name,
                    'image' => $user->image,
                    'date_of_birth' => $user->date_of_birth,
                    'gender' => $user->gender,
                    'contact' => $user->contact,
                    'address' => $user->address,
                    'city' => $user->city,
                    'country' => $user->country,
                    'postal_code' => $user->postal_code,
                    'nid_passport' => $user->nid_passport,
                    'status' => $user->status,
                    'merchant_status' => $user->merchant_status,
                    'kyc' => $user->kyc,
                    'consultant' => $user->consultant,
                    'ambassador' => $user->ambassador,
                    'elite_club' => $user->elite_club,
                    'elite_v2' => $user->is_elite_v2,
                    'angel_club' => $user->angel_club,
                    'email_verified' => $user->email_verified_at ? 'Verified' : 'Non Verified',
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function affiliatesList(Request $request)
    {
        $user = Auth::user();

        $query = User::where('sponsor_id', $user->id)
            ->select(
                'id',
                'name',
                'user_name',
                'email',
                'created_at',
            )
            ->latest();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('user_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('contact', 'like', "%{$search}%");
            });
        }

        $affiliates = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate list fetched successfully',

            'data' => $affiliates->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'name'       => $item->name,
                    'user_name'  => $item->user_name,
                    'email'      => $item->email,
                    'created_at' => $item->created_at->format('Y-m-d h:i A'),
                ];
            }),

            'pagination' => [
                'current_page' => $affiliates->currentPage(),
                'per_page'     => $affiliates->perPage(),
                'total'        => $affiliates->total(),
            ]
        ]);
    }


    // FORGOT PASSWORD
    public function forgotPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            // Generate Token
            $token = Str::random(64);

            // Delete old token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Insert new token
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]);

            // Reset Link
            $resetLink ='https://mindchainwallet.com/auth/reset-password?token=' . $token;

            // Send Mail
            Mail::send('emails.emailVerification', [
                'title' => 'Secure Password Reset',
                'subHeader' => 'Security Notification',
                'heading' => 'Reset Your Password',
                'messageText' => "We received a request to reset the password for your account. If you made this request, you can securely create a new password by clicking the button below.",

                'buttonText' => 'Reset Password',
                'buttonUrl' => $resetLink,
                'buttonColor' => '#4f46e5',

                'extraText' => "This link will expire in 20 minutes for your security. If you did not request a password reset, you can safely ignore this email. No changes have been made to your account."
            ], function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset Your Password');
            });
            return response()->json([
                'status' => true,
                'message' => 'Password reset link sent to email'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset Password
     */
    public function resetPassword(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'password' => 'required|min:8|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Find all reset records
            $reset = DB::table('password_reset_tokens')->get();

            $matched = null;

            foreach ($reset as $row) {
                if (Hash::check($request->token, $row->token)) {
                    $matched = $row;
                    break;
                }
            }

            if (!$matched) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid token'
                ], 400);
            }

            // Check expiry
            if (Carbon::parse($matched->created_at)->addMinutes(20)->isPast()) {

                DB::table('password_reset_tokens')
                    ->where('email', $matched->email)
                    ->delete();

                return response()->json([
                    'status' => false,
                    'message' => 'Token expired'
                ], 400);
            }

            // Update password
            $user = User::where('email', $matched->email)->first();

            $user->password = $request->password;
            $user->save();

            // delete token
            DB::table('password_reset_tokens')
                ->where('email', $matched->email)
                ->delete();

            return response()->json([
                'status' => true,
                'message' => 'Password reset successful'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change Password (Logged In User)
     */
    public function changePassword(Request $request)
    {
        try {

            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|min:8|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Check Current Password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update Password
            $user->update([
                'password' => $request->new_password
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function profileUpdate(Request $request)
    {
        try {

            $user = auth()->user();

            $validator = Validator::make($request->all(), [
                'name'          => 'nullable|string|max:255',
                'contact'       => 'nullable|string|max:100',
                'address'       => 'nullable|string|max:255',
                'city'          => 'nullable|string|max:150',
                'country'       => 'nullable|string|max:150',
                'postal_code'   => 'nullable|string|max:100',
                'date_of_birth' => 'nullable|date',
                'gender'        => 'nullable|string|max:10',
                'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $data = $validator->validated();

            /* =========================
               IMAGE HANDLE (Controller)
            ==========================*/
            if ($request->hasFile('image')) {

                $file = $request->file('image');

                // delete old image if exists
                if ($user->image && Storage::disk('public')->exists($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }

                // store new image
                $path = $file->store('users', 'public');

                $data['image'] = $path;
            }

            $user->update($data);

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'data' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
