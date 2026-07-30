<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DepositJob;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    public function handle(Request $request, $userId)
    {
        DB::beginTransaction();

        try {

            $txHash = trim($request->txHash);

            if (!$txHash) {
                throw new \Exception('txHash is required.');
            }



            $params = PaymentGatewayService::auth([
                'txHash' => $txHash,
            ]);

            $response = PaymentGatewayService::client()->get(
                rtrim(config('payment_gateway.base_url'), '/') . "api/v1/payments/{$txHash}",
                $params
            );


            if (!$response->successful()) {
                throw new \Exception('Gateway request failed.');
            }

            $payment = $response->json();

            if (!($payment['status'] ?? false)) {
                throw new \Exception('Invalid payment response.');
            }

            if (!in_array(strtolower($payment['payment_status']), ['completed'])) {

                DB::commit();

                return response()->json([
                    'status'  => false,
                    'message' => 'Payment not completed.',
                ]);
            }


            $deposit = DepositJob::where('invoice_id', $payment['invoice_id'])
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$deposit) {
                throw new \Exception('Deposit not found.');
            }

            if ($deposit->status === 'Completed') {

                DB::commit();

                return response()->json([
                    'status'  => true,
                    'message' => 'Already processed.',
                ]);
            }


            $exists = Transaction::where('txn_id', $txHash)
                ->lockForUpdate()
                ->exists();

            if (!$exists) {

                Transaction::create([
                    'user_id'     => $deposit->user_id,
                    'wallet'      => strtoupper($payment['token']),
                    'amount'      => $payment['amount'],
                    'type'        => 'Credit',
                    'method'      => 'Deposit',
                    'txn_id'      => $txHash,
                    'description' => $payment['amount'] . ' ' . strtoupper($payment['token_name']) . ' deposited via gateway',
                    'status'      => 'Approved',
                ]);
            }


            $deposit->update([
                'status'           => 'Completed',
                'paid_at'          => now(),
                'tx_hash'          => $txHash,
                'gateway_response' => $payment,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Deposit credited successfully.',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
