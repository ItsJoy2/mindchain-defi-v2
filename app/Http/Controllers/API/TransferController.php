<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Transaction;
use App\Mail\OtpMail;

use Mail;

class TransferController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Step 1 -> Send OTP
    |--------------------------------------------------------------------------
    */

    public function sendTransferOtp(Request $request)
    {
        try {

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

            $sender = Auth::user();

            $receiver = User::where('user_name', $request->user_name)->first();

            // Self transfer block
            if ($sender->id == $receiver->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot transfer to yourself'
                ], 422);
            }

            // Wallet mapping
            $walletColumns = [
                'MIND'  => 'mind_balance',
                'MUSD'  => 'musd_balance',
                'USDT'  => 'usdt_balance',
                'BMIND' => 'bmind_balance',
            ];

            $walletColumn = $walletColumns[$request->wallet];

            // Balance check
            if ($sender->$walletColumn < $request->amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance'
                ], 422);
            }

            // Generate OTP
            $otp = rand(100000, 999999);

            // Save pending transaction
            $transaction = Transaction::create([
                'user_id'           => $sender->id,
                'amount'            => $request->amount,
                'wallet'            => $request->wallet,
                'type'              => 'Debit',
                'method'            => 'User Transfer',
                'description'       => 'Transfer To ' . $receiver->user_name,
                'txn_id'            => 'TXN' . strtoupper(Str::random(10)),
                'kids_username'     => $receiver->user_name,
                'confirmation_code' => $otp,
                'status'            => 'Pending',
            ]);

            // Send OTP Mail
            Mail::to($sender->email)->send(new OtpMail($otp));

            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully',
                'txn_id' => $transaction->txn_id
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
    | Step 2 -> Confirm Transfer
    |--------------------------------------------------------------------------
    */

    public function confirmTransfer(Request $request)
    {
        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), [
                'txn_id' => 'required',
                'otp'    => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $sender = Auth::user();

            $transaction = Transaction::where('txn_id', $request->txn_id)
                ->where('user_id', $sender->id)
                ->where('status', 'Pending')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'status' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // OTP check
            if ($transaction->confirmation_code != $request->otp) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP'
                ], 422);
            }

            // Receiver
            $receiver = User::where('user_name', $transaction->kids_username)->first();

            if (!$receiver) {
                return response()->json([
                    'status' => false,
                    'message' => 'Receiver not found'
                ], 404);
            }

            // Wallet mapping
            $walletColumns = [
                'MIND'  => 'mind_balance',
                'MUSD'  => 'musd_balance',
                'USDT'  => 'usdt_balance',
                'BMIND' => 'bmind_balance',
            ];

            $walletColumn = $walletColumns[$transaction->wallet];

            // Balance check again
            if ($sender->$walletColumn < $transaction->amount) {

                $transaction->update([
                    'status' => 'Reject'
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance'
                ], 422);
            }

            // Deduct sender
            $sender->$walletColumn -= $transaction->amount;
            $sender->save();

            // Add receiver
            $receiver->$walletColumn += $transaction->amount;
            $receiver->save();

            // Update sender transaction
            $transaction->update([
                'status' => 'Approved',
                'confirmation_code' => null
            ]);

            // Receiver transaction
            Transaction::create([
                'user_id'       => $receiver->id,
                'amount'        => $transaction->amount,
                'wallet'        => $transaction->wallet,
                'type'          => 'Credit',
                'method'        => 'User Transfer',
                'description'   => 'Received From ' . $sender->user_name,
                'txn_id'        => 'TXN' . strtoupper(Str::random(10)),
                'kids_username' => $sender->user_name,
                'status'        => 'Approved',
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
