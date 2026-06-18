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

    protected $description = 'Check pending deposits and credit user wallets';

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

                if (!$deposit) {
                    DB::commit();
                    continue;
                }

                if ($deposit->status === 'Completed') {
                    DB::commit();
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Payment Status Check
                |--------------------------------------------------------------------------
                */

                $payload = [
                    'invoice_id' => $deposit->invoice_id
                ];

                $paymentResponse = PaymentGatewayService::client(
                    $payload
                )->get(
                    config('payment_gateway.api_url')
                    . '/api/payments/'
                    . $deposit->invoice_id
                );

                if (!$paymentResponse->successful()) {

                    throw new \Exception(
                        'Payment status check failed. HTTP: '
                        . $paymentResponse->status()
                    );
                }

                $paymentData = $paymentResponse->json();

                $paymentStatus = strtolower(
                    $paymentData['payment_status']
                    ?? $paymentData['status']
                    ?? 'pending'
                );

                if ($paymentStatus !== 'completed') {

                    DB::commit();

                    $this->line(
                        "Invoice {$deposit->invoice_id} still pending"
                    );

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Amount From Payment API
                |--------------------------------------------------------------------------
                */

                $amount = (float) (
                    $paymentData['received_amount']
                    ?? $paymentData['balance']
                    ?? $paymentData['amount']
                    ?? 0
                );

                if ($amount <= 0) {

                    throw new \Exception(
                        'Amount not found in payment response'
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

                if (!$exists) {

                    Transaction::create([
                        'user_id'     => $deposit->user_id,
                        'wallet_type' => strtoupper($deposit->wallet),
                        'amount'      => $amount,
                        'type'        => 'Credit',
                        'method'      => 'Deposit',
                        'trx_id'      => $deposit->invoice_id,
                        'description' => $amount . ' ' .
                            strtoupper($deposit->wallet) .
                            ' deposited via payment gateway',
                        'status'      => 'Approved',
                    ]);

                    $user = $deposit->user;

                    if ($user) {

                        $wallet = strtolower(
                            $deposit->wallet
                        );

                        if (isset($user->{$wallet})) {

                            $user->increment(
                                $wallet,
                                $amount
                            );
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Complete Deposit
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
                    "Deposit Completed : {$deposit->invoice_id} | Amount : {$amount}"
                );

            } catch (\Throwable $e) {

                DB::rollBack();

                $this->error(
                    "Invoice {$row->invoice_id} : "
                    . $e->getMessage()
                );

                report($e);
            }
        }

        return Command::SUCCESS;
    }
}
