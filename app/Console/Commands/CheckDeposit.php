<?php

namespace App\Console\Commands;

use App\Models\DepositJob;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDeposit extends Command
{
    protected $signature = 'deposit:check';
    protected $description = 'Check pending deposits and credit transactions';

    public function handle()
    {
        $deposits = DepositJob::where('status', 'Pending')
            ->whereNotNull('invoice_id')
            ->orderBy('id')
            ->limit(100)
            ->get();

        if ($deposits->isEmpty()) {
            $this->info('No pending deposits found');
            return Command::SUCCESS;
        }

        foreach ($deposits as $row) {

            DB::beginTransaction();

            try {

                $deposit = DepositJob::where('id', $row->id)
                    ->lockForUpdate()
                    ->first();

                if (!$deposit || $deposit->status !== 'Pending') {
                    DB::commit();
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | PAYMENT API CALL (NEW SIGNATURE SYSTEM)
                |--------------------------------------------------------------------------
                */

                $paymentResponse = PaymentGatewayService::client()
                    ->get(
                        config('payment_gateway.api_url')
                        . '/api/payments/'
                        . $deposit->invoice_id
                    );

                if (!$paymentResponse->successful()) {
                    throw new \Exception(
                        'Payment API failed: ' . $paymentResponse->status()
                    );
                }

                $paymentData = $paymentResponse->json();

                $status = strtolower($paymentData['payment_status'] ?? 'pending');

                /*
                |--------------------------------------------------------------------------
                | IF EXPIRED -> mark expired
                |--------------------------------------------------------------------------
                */

                if ($status === 'expired') {

                    $deposit->update([
                        'status' => 'Expired'
                    ]);

                    DB::commit();

                    $this->line("Invoice {$deposit->invoice_id} expired");

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | IF NOT COMPLETED -> skip
                |--------------------------------------------------------------------------
                */

                if ($status !== 'completed') {

                    DB::commit();

                    $this->line("Invoice {$deposit->invoice_id} still pending");

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | AMOUNT (IMPORTANT: NEVER IGNORE SMALL VALUES)
                |--------------------------------------------------------------------------
                */

                $amount = (float) (
                    $paymentData['balance']
                    ?? $paymentData['amount']
                    ?? $paymentData['received_amount']
                    ?? 0
                );

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT FIX:
                | DO NOT reject small values like 0.0001
                |--------------------------------------------------------------------------
                */

                if ($amount < 0) {
                    $amount = 0;
                }

                if ($amount <= 0) {
                    DB::commit();
                    $this->line("Invoice {$deposit->invoice_id} no balance found");
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | DUPLICATE CHECK
                |--------------------------------------------------------------------------
                */

                $exists = Transaction::where('txn_id', $deposit->invoice_id)->exists();

                if (!$exists) {

                    Transaction::create([
                        'user_id'     => $deposit->user_id,
                        'wallet_type' => strtoupper($deposit->wallet),
                        'amount'      => $amount,
                        'type'        => 'Credit',
                        'method'      => 'Deposit',
                        'txn_id'      => $deposit->invoice_id,
                        'description' => $amount . ' ' . strtoupper($deposit->wallet) . ' deposit via gateway',
                        'status'      => 'Approved',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | UPDATE DEPOSIT
                |--------------------------------------------------------------------------
                */

                $deposit->update([
                    'status'           => 'Completed',
                    'paid_at'          => now(),
                    'tx_hash'          => $paymentData['tx_hash'] ?? null,
                    'gateway_response' => $paymentData,
                ]);

                DB::commit();

                $this->info(
                    "Deposit Completed: {$deposit->invoice_id} | Amount: {$amount}"
                );

            } catch (\Throwable $e) {

                DB::rollBack();

                $this->error(
                    "Invoice {$row->invoice_id} : " . $e->getMessage()
                );

                report($e);
            }
        }

        return Command::SUCCESS;
    }
}
