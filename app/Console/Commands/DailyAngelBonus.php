<?php

namespace App\Console\Commands;

use App\Models\AngelStaking;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DailyAngelBonus extends Command
{
    protected $signature = 'angel:daily-bonus';

    protected $description = 'Distribute daily angel staking bonus';

    public function handle()
    {
        try {

            $stakings = AngelStaking::where('status', 1)->get();

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
                    ->where('method', 'Daily Angel Bonus')
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if ($alreadyReceivedToday) {

                    DB::commit();
                    continue;
                }

                // duration cross → switch to 25% APY
                if ($days > $staking->duration) {

                    $staking->daily_bonus = ($staking->amount * 25) / (100 * $staking->duration);
                    $staking->save();
                }

                // Daily bonus send
                Transaction::create([
                    'user_id' => $staking->user_id,
                    'amount' => $staking->daily_bonus,
                    'wallet' => 'MUSD',
                    'type' => 'Credit',
                    'method' => 'Daily Angel Bonus',
                    'status' => 'Approved',
                    'description' => $staking->daily_bonus . ' Daily Bonus for Angel Membership'
                ]);

                // received days increment
                $staking->increment('received_days');

                DB::commit();
            }

            $this->info('Daily angel bonus distributed successfully.');

            return Command::SUCCESS;

        } catch (\Exception $e) {

            DB::rollBack();

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
