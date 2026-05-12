<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TransferController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }


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
                'wallet'  => 'required|in:MIND,MUSD,USDT,BMIND',
                'amount'  => 'required|numeric|min:0.01',
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

            if (!$this->walletService->hasBalance($sender->id, $request->wallet, $request->amount)) {

                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance'
                ], 422);
            }

            $otp = rand(100000, 999999);

            // remove old pending
            Transaction::where('user_id', $sender->id)
                ->where('method', 'User Transfer')
                ->where('status', 'Pending')
                ->delete();

            Transaction::create([
                'user_id'           => $sender->id,
                'receiver_id'       => $receiver->id,
                'amount'            => -$request->amount,
                'wallet'            => $request->wallet,
                'type'              => 'Debit',
                'method'            => 'User Transfer',
                'description'       =>  -$request->amount . ' ' . $request->wallet . ' Transfer To ' . $receiver->user_name,
                'txn_id'            => strtoupper(Str::random(10)),
                'confirmation_code' => $otp,
                'status'            => 'Pending',
            ]);

            if ($sender->email) {
                Mail::to($sender->email)->send(
                    new OtpMail(
                        $otp,
                        'Transfer OTP',
                        'Secure Wallet Transfer',
                        'Use this OTP to confirm your transfer'
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

            $receiver = User::find($transaction->receiver_id);

            if (!$receiver) {
                return response()->json([
                    'status' => false,
                    'message' => 'Receiver not found'
                ], 404);
            }

            $wallet = $transaction->wallet;
            $amount = abs($transaction->amount);

            if (!$this->walletService->hasBalance($sender->id, $wallet, $amount)) {

                $transaction->update(['status' => 'Reject']);

                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient balance'
                ], 422);
            }

            // CREDIT RECEIVER
            Transaction::create([
                'user_id'       => $receiver->id,
                'amount'        => $amount,
                'wallet'        => $wallet,
                'type'          => 'Credit',
                'method'        => 'User Transfer',
                'description'   => $amount . ' ' . $wallet . ' Received From ' . $sender->user_name,
                'txn_id'        => strtoupper(Str::random(10)),
                'status'        => 'Approved',
            ]);

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
