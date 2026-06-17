<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DepositJob;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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

            // Already Processed
            if ($deposit->status === 'Completed') {

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Already processed'
                ]);
            }

            // Check Payment Status
            $timestamp = time();

            $signature = hash_hmac(
                'sha256',
                $timestamp . json_encode([
                    'invoice_id' => $invoiceId
                ]),
                config('payment_gateway.secret')
            );

            $paymentResponse = Http::timeout(20)
                ->withHeaders([
                    'X-LICENSE-KEY' => config('payment_gateway.license_key'),
                    'X-TIMESTAMP'   => $timestamp,
                    'X-SIGNATURE'   => $signature,
                ])
                ->get(
                    config('payment_gateway.api_url')
                    . '/api/payments/'
                    . $invoiceId
                );

            if (!$paymentResponse->successful()) {
                throw new \Exception('Unable to verify payment status');
            }

            $paymentData = $paymentResponse->json();

            $paymentStatus = strtolower(
                $paymentData['payment_status'] ?? 'pending'
            );

            if ($paymentStatus !== 'completed') {

                DB::commit();

                return response()->json([
                    'status' => false,
                    'message' => 'Payment not completed',
                    'payment_status' => $paymentStatus
                ]);
            }

            // Check Wallet Balance
            $balancePayload = [
                'chain_id' => $deposit->chain_id,
                'type'     => $deposit->type,
                'address'  => $deposit->wallet_address,
            ];

            if (!empty($deposit->contract_address)) {
                $balancePayload['contract_address']
                    = $deposit->contract_address;
            }

            $payload = json_encode($balancePayload);

            $timestamp = time();

            $signature = hash_hmac(
                'sha256',
                $timestamp . $payload,
                config('payment_gateway.secret')
            );

            $balanceResponse = Http::timeout(20)
                ->withHeaders([
                    'X-LICENSE-KEY' => config('payment_gateway.license_key'),
                    'X-TIMESTAMP'   => $timestamp,
                    'X-SIGNATURE'   => $signature,
                ])
                ->get(
                    config('payment_gateway.api_url')
                    . '/api/check-balance',
                    $balancePayload
                );

            if (!$balanceResponse->successful()) {
                throw new \Exception('Balance check failed');
            }

            // Get the balance amount
            $amount = (float) trim($balanceResponse->body());

            if ($amount <= 0) {
                throw new \Exception('Wallet balance is zero');
            }

            // Check if transaction already exists
            $exists = Transaction::where(
                'trx_id',
                $deposit->invoice_id
            )->exists();

            if ($exists) {

                $deposit->update([
                    'status'  => 'Completed',
                    'paid_at' => now(),
                ]);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Already credited'
                ]);
            }

            // Create Transaction and Update Deposit
            Transaction::create([
                'user_id'     => $deposit->user_id,
                'wallet_type' => strtoupper($deposit->wallet),
                'amount'      => $amount,
                'type'        => 'Credit',
                'method'      => 'Deposit',
                'trx_id'      => $deposit->invoice_id,
                'description' => $amount . ' ' . strtoupper($deposit->wallet) . ' deposited via payment gateway',
                'status'      => 'Approved',
            ]);

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

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
