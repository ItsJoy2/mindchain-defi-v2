<?php

namespace App\Console\Commands;

use App\Models\LiquidityPool;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReleaseLiquidityPools extends Command
{
    protected $signature = 'liquidity:release';

    protected $description = 'Release matured Liquidity Pool investments and credit the total return to user wallets';

    public function handle()
    {
        $this->info('Starting Liquidity Pool release process...');

        $releasedCount = 0;
        $failedCount = 0;

        LiquidityPool::query()
            ->where('status', 'Active')
            ->where('release_at', '<=', now())
            ->orderBy('id')
            ->chunkById(100, function ($pools) use (&$releasedCount, &$failedCount) {

                foreach ($pools as $pool) {

                    try {

                        DB::transaction(function () use ($pool, &$releasedCount) {

                            $lockedPool = LiquidityPool::where('id', $pool->id)
                                ->lockForUpdate()
                                ->first();

                            if (!$lockedPool) {
                                return;
                            }

                            if ($lockedPool->status !== 'Active') {
                                return;
                            }

                            if (!$lockedPool->release_at ||
                                $lockedPool->release_at->isFuture()) {
                                return;
                            }

                            $returnAmount = (float) $lockedPool->total_return_amount;

                            if ($returnAmount <= 0) {
                                throw new \Exception(
                                    "Invalid return amount for Liquidity Pool #{$lockedPool->id}"
                                );
                            }

                            Transaction::create([
                                'user_id'    => $lockedPool->user_id,
                                'wallet'     => $lockedPool->wallet,
                                'amount'     => $returnAmount,
                                'type'       => 'Credit',
                                'method'     => 'Liquidity Pool Release',
                                'description' => "Liquidity Pool released " . number_format($lockedPool->total_return_amount, 2) . " {$lockedPool->wallet}.",
                                'status'     => 'Approved',
                                'txn_id'     => 'LP-RELEASE-' . $lockedPool->id . '-' . uniqid(),
                            ]);

                            /*
                             * Mark pool as Released.
                             */
                            $lockedPool->update([
                                'status'      => 'Released',
                                'released_at' => now(),
                            ]);

                            $releasedCount++;

                            $this->info(
                                "Released Liquidity Pool #{$lockedPool->id} | " .
                                "User ID: {$lockedPool->user_id} | " .
                                "Wallet: {$lockedPool->wallet} | " .
                                "Amount: {$returnAmount}"
                            );
                        }, 3);

                    } catch (Throwable $e) {

                        $failedCount++;

                        report($e);

                        $this->error(
                            "Failed to release Liquidity Pool #{$pool->id}: " .
                            $e->getMessage()
                        );
                    }
                }
            });

        $this->info("Liquidity Pool release process completed.");
        $this->info("Released: {$releasedCount}");
        $this->info("Failed: {$failedCount}");

        return self::SUCCESS;
    }
}
