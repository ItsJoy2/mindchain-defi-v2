<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DepositJob;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, $userId)
    {
        DB::beginTransaction();

        try {

            $txHash = trim((string) $request->input('txHash'));

            if (empty($txHash)) {
                throw new \Exception('txHash is required.');
            }

            DepositJob::where('user_id', $userId)
                ->whereNull('tx_hash')
                ->latest('id')
                ->update([
                    'tx_hash' => $txHash,
                ]);

            DB::beginTransaction();

            try {

                DB::commit();

            } catch (\Throwable $e) {

                DB::rollBack();

                throw $e;
            }

            $already = DepositJob::where('tx_hash', $txHash)
                ->where('status', 'Completed')
                ->lockForUpdate()
                ->first();

            if ($already) {

                DB::commit();

                return response()->json([
                    'status'  => true,
                    'message' => 'Already processed.',
                ]);
            }


            $params = PaymentGatewayService::auth([
                'txHash' => $txHash,
            ]);

            $response = PaymentGatewayService::client()->get(
                rtrim(config('payment_gateway.api_url'), '/') . "/api/v1/payments/{$txHash}",
                $params
            );

            Log::info('Gateway Response', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            if (!$response->successful()) {
                throw new \Exception('Gateway request failed.');
            }

            $payment = $response->json();

            if (!($payment['status'] ?? false)) {
                throw new \Exception($payment['message'] ?? 'Invalid payment response.');
            }

            if (strtolower($payment['payment_status']) !== 'completed') {

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

            if ($deposit->tx_hash !== $txHash) {

                $deposit->tx_hash = $txHash;
                $deposit->save();
            }

            if ($deposit->status === 'Completed') {

                DB::commit();

                return response()->json([
                    'status'  => true,
                    'message' => 'Already processed.',
                ]);
            }


            if ((float) $deposit->amount != (float) $payment['amount']) {
                throw new \Exception('Amount mismatch.');
            }

            if (strtoupper($deposit->wallet) != strtoupper($payment['token_name'])) {
                throw new \Exception('Wallet mismatch.');
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
                'gateway_response' => $payment,
            ]);

            DB::commit();

            return response()->json([
                'status'     => true,
                'message'    => 'Deposit credited successfully.',
                'invoice_id' => $deposit->invoice_id,
                'tx_hash'    => $txHash,
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
