<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AngelStaking;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DailyAngelBonus extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'angel:bonus';

    /**
     * The console command description.
     */
    protected $description = 'Distribute daily angel bonus';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            DB::beginTransaction();

            $stakings = AngelStaking::where('status', 1)->get();

            foreach ($stakings as $staking) {

                /**
                 * Unlimited Duration
                 * duration = 0 means unlimited bonus
                 */
                if ($staking->duration == 0) {

                    Transaction::create([
                        'user_id' => $staking->user_id,
                        'amount' => number_format($staking->daily_bonus, 4),
                        'wallet' => 'MUSD',
                        'type' => 'Credit',
                        'method' => 'Angel Daily Bonus',
                        'status' => 'Approved',
                        'description' => number_format($staking->daily_bonus, 2) . ' MUSD Daily Bonus from Angel Club'
                    ]);

                    continue;
                }

                /**
                 * Limited Duration
                 */
                if ($staking->received_days < $staking->duration) {

                    Transaction::create([
                        'user_id' => $staking->user_id,
                        'amount' => number_format($staking->daily_bonus, 4),
                        'wallet' => 'MUSD',
                        'type' => 'Credit',
                        'method' => 'Angel Daily Bonus',
                        'status' => 'Approved',
                        'description' => number_format($staking->daily_bonus, 2) . ' MUSD Daily Bonus from Angel Club'
                    ]);

                    // Increase received days
                    $staking->increment('received_days');

                    // Complete staking
                    if (($staking->received_days + 1) >= $staking->duration) {

                        $staking->status = 0;
                        $staking->save();
                    }
                }
            }

            DB::commit();

            $this->info('Daily angel bonus distributed successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            $this->error($e->getMessage());
        }
    }
}
