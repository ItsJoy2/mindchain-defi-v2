<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DepositJob;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        DB::beginTransaction();

        try {

            $deposit = DepositJob::where(
                'invoice_id',
                $request->invoice_id
            )->lockForUpdate()->first();

            if (!$deposit) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            if ($deposit->status === 'paid') {
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Already processed'
                ]);
            }

            if ($request->status !== 'paid') {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Payment not completed'
                ]);
            }

            $walletType = strtoupper(
                $request->token_name
                    ?? $deposit->wallet
                    ?? 'USDT'
            );

            $walletColumn = strtolower($walletType) . '_wallet';

            $deposit->update([
                'status' => 'paid',
                'tx_hash' => $request->tx_hash,
                'paid_at' => now(),
            ]);

            if (!isset($user->{$walletColumn})) {
                throw new \Exception("Wallet column not found: {$walletColumn}");
            }

            $user = $deposit->user;

            // $user->increment(
            //     $walletColumn,
            //     $request->received_amount
            // );

            Transaction::create([
                'user_id' => $user->id,
                'wallet_type' => $walletType,
                'amount' => $request->received_amount,
                'type' => 'Credit',
                'remark' => 'Deposit',
                'trx_id' => $request->tx_hash,
                'description' => $request->received_amount . ' ' . $walletType . ' deposited via payment gateway',
                'status' => 'Approved',
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Deposit credited'
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
