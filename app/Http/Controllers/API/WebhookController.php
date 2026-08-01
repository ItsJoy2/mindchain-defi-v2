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
    public function handle(Request $request, $userId)
    {
        try {

            $invoiceId = trim((string) $request->input('invoice_id'));
            $txHash    = trim((string) $request->input('txHash'));

            if (!$invoiceId || !$txHash) {
                throw new \Exception('invoice_id and txHash are required.');
            }


            $deposit = DepositJob::where('invoice_id', $invoiceId)
                ->where('user_id', $userId)
                ->first();

            if (!$deposit) {
                throw new \Exception('Deposit not found.');
            }


            if (empty($deposit->tx_hash)) {
                $deposit->tx_hash = $txHash;
                $deposit->save();
            }


            if ($deposit->status === 'Completed') {
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

            if (!$response->successful()) {
                throw new \Exception($response->body());
            }

            $payment = $response->json();

            if (!($payment['status'] ?? false)) {
                throw new \Exception($payment['message'] ?? 'Invalid payment response.');
            }

            if (strtolower($payment['payment_status']) !== 'completed') {
                return response()->json([
                    'status'  => false,
                    'message' => 'Payment not completed.',
                ]);
            }



            DB::beginTransaction();

            $deposit = DepositJob::where('id', $deposit->id)
                ->lockForUpdate()
                ->first();


            // if ((float)$deposit->amount != (float)$payment['amount']) {
            //     throw new \Exception('Amount mismatch.');
            // }


            // if (strtoupper($deposit->wallet) != strtoupper($payment['token'])) {
            //     throw new \Exception('Wallet mismatch.');
            // }


            Transaction::create([
                'user_id'     => $deposit->user_id,
                'wallet'      => strtoupper($payment['token']),
                'amount'      => $payment['amount'],
                'type'        => 'Credit',
                'method'      => 'Deposit',
                'txn_id'      => $txHash,
                'description' => "{$payment['amount']} {$payment['token']} deposited via gateway",
                'status'      => 'Approved',
            ]);


            $deposit->update([
                'amount'           => $payment['amount'],
                'wallet'           => strtoupper($payment['token']),
                'status'           => 'Completed',
                'paid_at'          => now(),
                'gateway_response' => $payment,
            ]);

            DB::commit();

            return response()->json([
                'status'     => true,
                'message'    => 'Deposit credited successfully.',
                'invoice_id' => $invoiceId,
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
