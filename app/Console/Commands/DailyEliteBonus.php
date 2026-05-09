<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Transaction;
use App\Models\UsdtStakingHistory;

class DailyEliteBonus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elite:daily-bonus';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Distribute daily elite staking bonus';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {


            $stakings = UsdtStakingHistory::where('method','!=','Angel Membership')->get();

            if ($stakings->isEmpty()) {

                $this->info('No active staking found.');

                return Command::SUCCESS;
            }

            foreach ($stakings as $staking) {

                DB::beginTransaction();

                if ($staking->status === 'Expired') {
                    DB::commit();
                    continue;
                }

                $alreadyReceivedToday = Transaction::where('user_id', $staking->user_id)
                    ->where('method', 'Daily Elite Bonus')
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if ($alreadyReceivedToday) {
                    DB::commit();
                    continue;
                }


                if ($staking->received_days >= $staking->duration) {

                    $staking->daily_bonus = ($staking->amount * 15) / (100 * $staking->duration);
                }

                Transaction::create([
                    'user_id' => $staking->user_id,
                    'amount' => $staking->daily_bonus,
                    'wallet' => $staking->wallet,
                    'type' => 'Credit',
                    'method' => 'Daily Elite Bonus',
                    'status' => 'Approved',
                    'description' =>  '$' . $staking->daily_bonus . ' Daily Bonus for Elite Membership'
                ]);


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
