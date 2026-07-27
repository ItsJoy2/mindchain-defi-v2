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

    protected $description = 'Check pending deposits and process transactions';

    public function handle()
    {
        $deposits = DepositJob::where('status', 'Pending')
            ->whereNotNull('invoice_id')
            ->orderBy('id')
            ->limit(100)
            ->get();

        if ($deposits->isEmpty()) {
            $this->info('No pending deposits found.');
            return Command::SUCCESS;
        }

        foreach ($deposits as $row) {

            DB::beginTransaction();

            try {

                $deposit = DepositJob::lockForUpdate()->find($row->id);

                if (!$deposit || $deposit->status !== 'Pending') {
                    DB::commit();
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Gateway Request
                |--------------------------------------------------------------------------
                */


                $invoiceId = $deposit->invoice_id;

                $payload = [
                    'id' => $invoiceId,
                ];

                $paymentResponse = PaymentGatewayService::client()->get(
                    config('payment_gateway.api_url') . '/api/v1/payments/' . $invoiceId,
                    array_merge(
                        $payload,
                        PaymentGatewayService::auth($payload)
                    )
                );
                if (!$paymentResponse->successful()) {
                    throw new \Exception(
                        'Gateway HTTP ' .
                        $paymentResponse->status() .
                        ' : ' .
                        $paymentResponse->body()
                    );
                }

                $gateway = $paymentResponse->json();

                if (!($gateway['status'] ?? false)) {
                    throw new \Exception(
                        $gateway['message'] ?? 'Gateway Error'
                    );
                }

                $data = $gateway['data'] ?? [];

                $status = strtolower($data['payment_status'] ?? 'pending');
                /*
                |--------------------------------------------------------------------------
                | Pending
                |--------------------------------------------------------------------------
                */

                if ($status == 'pending') {

                    DB::commit();

                    $this->line("Pending : {$invoiceId}");

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Expired
                |--------------------------------------------------------------------------
                */

                if ($status == 'expired') {

                    $deposit->update([
                        'status' => 'Expired',
                        'gateway_response' => $gateway,
                    ]);

                    DB::commit();

                    $this->warn("Expired : {$invoiceId}");

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Only Completed
                |--------------------------------------------------------------------------
                */

                if ($status != 'completed') {

                    DB::commit();

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Amount
                |--------------------------------------------------------------------------
                */

                $amount = (float) data_get($paymentResponse, 'data.amount', 0);

                $txHash = data_get($paymentResponse, 'data.tx_hash');

                /*
                |--------------------------------------------------------------------------
                | Transaction
                |--------------------------------------------------------------------------
                */

                if (!Transaction::where('txn_id', $invoiceId)->exists()) {

                    Transaction::create([
                        'user_id'     => $deposit->user_id,
                        'wallet' => strtoupper($deposit->wallet),
                        'amount'      => $amount,
                        'type'        => 'Credit',
                        'method'      => 'Deposit',
                        'txn_id'      => $invoiceId,
                        'description' => "{$amount} {$deposit->wallet} Deposit via Payment Gateway",
                        'status'      => 'Approved',
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Update Deposit
                |--------------------------------------------------------------------------
                */

                $deposit->update([
                    'status'           => 'Completed',
                    'paid_at'          => now(),
                    'tx_hash'          => $data['tx_hash'] ?? null,
                    'gateway_response' => $gateway,
                ]);

                DB::commit();

                $this->info(
                    "Completed : {$invoiceId} | Amount : {$amount}"
                );

            } catch (\Throwable $e) {

                DB::rollBack();

                $this->error(
                    "{$row->invoice_id} => " . $e->getMessage()
                );

                report($e);
            }
        }

        return Command::SUCCESS;
    }
}
