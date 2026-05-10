<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Transaction;
use App\Models\EliteV2StakingHistory;

class DailyEliteV2Bonus extends Command
{
    protected $signature = 'elitev2:daily-bonus';

    protected $description = 'Distribute daily elite V2 staking bonus';

    public function handle()
    {
        try {

            $stakings = EliteV2StakingHistory::where('status', 'Approved')->get();

            if ($stakings->isEmpty()) {

                $this->info('No active staking found.');

                return Command::SUCCESS;
            }

            foreach ($stakings as $staking) {

                DB::beginTransaction();

                $createdAt = Carbon::parse($staking->created_at);

                $today = Carbon::now();

                $days = $createdAt->diffInDays($today);

                // Already received today check
                $alreadyReceivedToday = Transaction::where('user_id', $staking->user_id)
                    ->where('method', 'Daily Elite V2 Bonus')
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if ($alreadyReceivedToday) {

                    DB::commit();

                    continue;
                }

                if ($days > $staking->duration) {

                    $staking->daily_bonus = ($staking->amount * 20) / (100 * $staking->duration);

                    $staking->save();
                }

                // Daily bonus send
                Transaction::create([
                    'user_id' => $staking->user_id,
                    'amount' => $staking->daily_bonus,
                    'wallet' => $staking->wallet,
                    'type' => 'Credit',
                    'method' => 'Daily Elite V2 Bonus',
                    'status' => 'Approved',
                    'description' => '$' . $staking->daily_bonus . ' Daily Bonus for Elite V2 Membership'
                ]);

                // received days increment
                $staking->increment('received_days');

                DB::commit();
            }

            $this->info('Daily elite V2 bonus distributed successfully.');

            return Command::SUCCESS;

        } catch (\Exception $e) {

            DB::rollBack();

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
