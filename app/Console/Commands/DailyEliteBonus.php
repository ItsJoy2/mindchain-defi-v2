<?php

namespace App\Console\Commands;

use App\Models\EliteStaking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Transaction;

class DailyEliteBonus extends Command
{
    protected $signature = 'elite:daily-bonus';

    protected $description = 'Distribute daily elite staking bonus';

    public function handle()
    {
        try {

            $stakings = EliteStaking::where('wallet', 'USDT')->where('status', 1)->get();

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
                    ->where('method', 'Daily Elite Bonus')
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if ($alreadyReceivedToday) {

                    DB::commit();
                    continue;
                }

                // duration cross → switch to 15% APY
                if ($days > $staking->duration) {

                    $staking->daily_bonus = ($staking->amount * 15) / (100 * $staking->duration);

                    $staking->save();
                }

                // Daily bonus send
                Transaction::create([
                    'user_id' => $staking->user_id,
                    'amount' => $staking->daily_bonus,
                    'wallet' => $staking->wallet,
                    'type' => 'Credit',
                    'method' => 'Daily Elite Bonus',
                    'status' => 'Approved',
                    'description' => '$' . $staking->daily_bonus . ' Daily Bonus for Elite Membership'
                ]);

                // received days increment
                $staking->increment('received_days');

                DB::commit();
            }

            $this->info('Daily elite bonus distributed successfully.');

            return Command::SUCCESS;

        } catch (\Exception $e) {

            DB::rollBack();

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
