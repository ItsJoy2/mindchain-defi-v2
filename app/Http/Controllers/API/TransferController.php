<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Transaction;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TransferController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | SEND OTP
    |--------------------------------------------------------------------------
    */

    public function sendTransferOtp(Request $request)
    {
        try {

            $sender = Auth::user();

            if (!$sender) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'user_name' => 'required|exists:users,user_name',
                'wallet'    => 'required|in:MIND,MUSD,USDT,BMIND',
                'amount'    => 'required|numeric|min:0.01',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $receiver = User::where('user_name', $request->user_name)->first();

            if (!$receiver) {
                return response()->json([
                    'status' => false,
                    'message' => 'Receiver not found'
                ], 404);
            }

            if ($sender->id == $receiver->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot transfer to yourself'
                ], 422);
            }

            $wallet = $request->wallet;

            // BALANCE CHECK
            $balance = Transaction::where('user_id', $sender->id)
                ->where('wallet', $wallet)
                ->sum('amount') ?? 0;

            if ($balance < $request->amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance'
                ], 422);
            }

            // OTP
            $otp = rand(100000, 999999);

            // DELETE OLD PENDING
            Transaction::where('user_id', $sender->id)
                ->where('status', 'Pending')
                ->where('method', 'User Transfer')
                ->delete();

            // STORE TEMP TRANSACTION
            Transaction::create([
                'user_id'           => $sender->id,
                'amount'            => $request->amount,
                'wallet'            => $wallet,
                'type'              => 'Debit',
                'method'            => 'User Transfer',
                'description'       => 'Transfer To ' . $receiver->user_name,
                'txn_id'            => 'TXN' . strtoupper(Str::random(10)),
                'kids_username'     => $receiver->user_name,
                'confirmation_code' => $otp,
                'status'            => 'Pending',
            ]);

            // SEND MAIL (SAFE)
            if ($sender->email) {
                Mail::to($sender->email)->send(
                    new OtpMail(
                        $otp,
                        'Transfer OTP',
                        'Secure Transfer Verification',
                        'Use this OTP to complete your transfer'
                    )
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully'
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CONFIRM OTP ONLY
    |--------------------------------------------------------------------------
    */

    public function confirmTransfer(Request $request)
    {
        DB::beginTransaction();

        try {

            $sender = Auth::user();

            if (!$sender) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'otp' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // FIND BY OTP ONLY
            $transaction = Transaction::where('user_id', $sender->id)
                ->where('confirmation_code', $request->otp)
                ->where('status', 'Pending')
                ->latest()
                ->first();

            if (!$transaction) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP'
                ], 422);
            }

            $receiver = User::where('user_name', $transaction->kids_username)->first();

            if (!$receiver) {
                return response()->json([
                    'status' => false,
                    'message' => 'Receiver not found'
                ], 404);
            }

            $wallet = $transaction->wallet;

            // RECHECK BALANCE
            $balance = Transaction::where('user_id', $sender->id)
                ->where('wallet', $wallet)
                ->sum('amount') ?? 0;

            if ($balance < $transaction->amount) {

                $transaction->update(['status' => 'Reject']);

                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance'
                ], 422);
            }

            // DEBIT SENDER
            Transaction::create([
                'user_id'       => $sender->id,
                'amount'        => $transaction->amount,
                'wallet'        => $wallet,
                'type'          => 'Debit',
                'method'        => 'User Transfer',
                'description'   => 'Transfer To ' . $receiver->user_name,
                'txn_id'        => 'TXN' . strtoupper(Str::random(10)),
                'status'        => 'Approved',
            ]);

            // CREDIT RECEIVER
            Transaction::create([
                'user_id'       => $receiver->id,
                'amount'        => $transaction->amount,
                'wallet'        => $wallet,
                'type'          => 'Credit',
                'method'        => 'User Transfer',
                'description'   => 'Received From ' . $sender->user_name,
                'txn_id'        => 'TXN' . strtoupper(Str::random(10)),
                'status'        => 'Approved',
            ]);

            // CLOSE OTP
            $transaction->update([
                'status' => 'Approved',
                'confirmation_code' => null
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Transfer successful'
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
