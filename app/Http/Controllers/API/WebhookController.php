<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DepositJob;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        DB::beginTransaction();

        try {

            $invoiceId = $request->invoice_id;

            if (!$invoiceId) {
                throw new \Exception('Invoice ID is required');
            }

            $deposit = DepositJob::where('invoice_id', $invoiceId)
                ->lockForUpdate()
                ->first();

            if (!$deposit) {

                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Invoice not found'
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | Already Processed
            |--------------------------------------------------------------------------
            */

            if ($deposit->status === 'Completed') {

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Already processed'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Payment Status Check
            |--------------------------------------------------------------------------
            */

            $paymentPayload = [
                'invoice_id' => $invoiceId
            ];

            $paymentResponse = PaymentGatewayService::client(
                $paymentPayload
            )->get(
                config('payment_gateway.api_url')
                . '/api/payments/'
                . $invoiceId
            );

            if (!$paymentResponse->successful()) {
                throw new \Exception(
                    'Unable to verify payment status'
                );
            }

            $paymentData = $paymentResponse->json();

            $paymentStatus = strtolower(
                $paymentData['payment_status']
                ?? 'pending'
            );

            if ($paymentStatus !== 'completed') {

                DB::commit();

                return response()->json([
                    'status' => false,
                    'message' => 'Payment not completed',
                    'payment_status' => $paymentStatus
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Get Amount
            |--------------------------------------------------------------------------
            */

            $amount = (float) (
                $paymentData['received_amount']
                ?? $paymentData['amount']
                ?? 0
            );

            /*
            |--------------------------------------------------------------------------
            | Fallback Balance Check
            |--------------------------------------------------------------------------
            */

            if ($amount <= 0) {

                $balancePayload = [
                    'chain_id' => $deposit->chain_id,
                    'type'     => $deposit->type,
                    'address'  => $deposit->wallet_address,
                ];

                if (!empty($deposit->contract_address)) {
                    $balancePayload['contract_address']
                        = $deposit->contract_address;
                }

                $balanceResponse = PaymentGatewayService::client(
                    $balancePayload
                )->get(
                    config('payment_gateway.api_url')
                    . '/api/check-balance',
                    $balancePayload
                );

                if (!$balanceResponse->successful()) {
                    throw new \Exception(
                        'Balance check failed'
                    );
                }

                $amount = (float) trim(
                    $balanceResponse->body()
                );
            }

            if ($amount <= 0) {
                throw new \Exception(
                    'Wallet balance is zero'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Duplicate Transaction Check
            |--------------------------------------------------------------------------
            */

            $exists = Transaction::where(
                'trx_id',
                $deposit->invoice_id
            )->lockForUpdate()->exists();

            if ($exists) {

                $deposit->update([
                    'status'  => 'Completed',
                    'paid_at' => now(),
                    'tx_hash' => $paymentData['tx_hash'] ?? null,
                    'gateway_response' => $paymentData,
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Already credited'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Create Transaction
            |--------------------------------------------------------------------------
            */

            Transaction::create([
                'user_id'     => $deposit->user_id,
                'wallet_type' => strtoupper($deposit->wallet),
                'amount'      => $amount,
                'type'        => 'Credit',
                'method'      => 'Deposit',
                'txn_id'      => $deposit->invoice_id,
                'description' => $amount . ' '
                    . strtoupper($deposit->wallet)
                    . ' deposited via payment gateway',
                'status'      => 'Approved',
            ]);


            // Credit User Wallet
            $user = $deposit->user;

            if ($user) {

                $wallet = strtolower($deposit->wallet);

                if (
                    in_array(
                        $wallet,
                        ['mind', 'musd', 'usdt', 'bmind']
                    )
                ) {
                    $user->increment(
                        $wallet,
                        $amount
                    );
                }
            }

            //Complete Deposit

            $deposit->update([
                'status'           => 'Completed',
                'tx_hash'          => $paymentData['tx_hash'] ?? null,
                'paid_at'          => now(),
                'gateway_response' => $paymentData,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Deposit credited successfully',
                'amount'  => $amount
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
