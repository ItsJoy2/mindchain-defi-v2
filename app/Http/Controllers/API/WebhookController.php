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

            $invoiceId = trim($request->invoice_id);
            $status    = strtolower((string)$request->status);
            $amount    = (float)$request->amount;
            $txHash    = $request->txHash;

            if (!$invoiceId) {
                throw new \Exception('Invoice ID is required.');
            }

            $deposit = DepositJob::where('invoice_id', $invoiceId)
                ->lockForUpdate()
                ->first();

            if (!$deposit) {
                throw new \Exception('Deposit not found.');
            }

            if ($deposit->status === 'Completed') {

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Already processed.'
                ]);
            }

            if (!in_array($status, ['completed', 'true', '1'])) {

                DB::commit();

                return response()->json([
                    'status' => false,
                    'message' => 'Payment not completed.'
                ]);
            }

            if ($amount <= 0) {
                throw new \Exception('Invalid amount.');
            }

            $exists = Transaction::where('txn_id', $deposit->invoice_id)
                ->lockForUpdate()
                ->exists();

            if (!$exists) {

                Transaction::create([
                    'user_id'     => $deposit->user_id,
                    'wallet'      => strtoupper($deposit->wallet),
                    'amount'      => $amount,
                    'type'        => 'Credit',
                    'method'      => 'Deposit',
                    'txn_id'      => $deposit->invoice_id,
                    'description' => "{$amount} " . strtoupper($deposit->wallet) . " deposited via gateway",
                    'status'      => 'Approved',
                ]);
            }

            $deposit->update([
                'status'           => 'Completed',
                'paid_at'          => now(),
                'tx_hash'          => $txHash,
                'gateway_response' => $request->all(),
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Deposit credited successfully.',
                'amount' => $amount,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
