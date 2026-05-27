<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseStaking;
use App\Models\Transaction;
use App\Models\AmbassadorHistory;
use App\Models\User;

class MindDailyStaking extends Command
{
    protected $signature = 'mind:staking-daily';
    protected $description = 'Mind Daily Staking Cron';

    public function handle()
    {
        try {

            $purchases = PurchaseStaking::where('status', 1)->get();

            foreach ($purchases as $row) {

                if (!$row || !$row->user_id) {
                    continue;
                }

                $user = User::find($row->user_id);

                if (!$user) {
                    continue;
                }

                $duration = (int) $row->duration;
                $received = (int) $row->received_days;

                // ACTIVE STAKING
                if ($received < $duration) {

                    // DAILY BONUS
                    Transaction::create([
                        'user_id'     => $user->id,
                        'wallet'      => 'MIND',
                        'amount'      => $row->daily,
                        'method'      => 'Daily Staking Bonus',
                        'type'        => 'Credit',
                        'status'      => 'Approved',
                        'description' => $row->daily . ' MIND Token Daily Bonus for staking',
                    ]);

                    // SPONSOR BONUS
                    $sponsor = $user->sponsor_id
                        ? User::find($user->sponsor_id)
                        : null;

                    if ($sponsor && $row->seller_bonus_rate > 0) {

                        $sellerBonus = number_format($row->daily * ($row->seller_bonus_rate / 100), 2);

                        Transaction::create([
                            'user_id'     => $sponsor->id,
                            'wallet'      => 'MIND',
                            'amount'      => $sellerBonus,
                            'method'      => 'Daily Seller Bonus',
                            'type'        => 'Credit',
                            'status'      => 'Approved',
                            'description' => $sellerBonus . ' MIND Token Daily Seller Bonus from ' . $user->user_name,
                        ]);
                    }

                    // INCREMENT RECEIVED DAYS
                    $row->increment('received_days');
                }

                // SETTLEMENT (EXPIRE)
                else {

                    if ($row->status == 1) {

                        if ($user->ambassador == 1) {

                            AmbassadorHistory::create([
                                'user_id'     => $user->id,
                                'wallet'      => 'MIND',
                                'amount'      => $row->amount,
                                'method'      => 'Token Settlement',
                                'type'        => 'Credit',
                                'status'      => 'approved',
                                'description' => $row->amount . ' MIND Token settlement bonus for staking',
                            ]);

                        } else {

                            Transaction::create([
                                'user_id'     => $user->id,
                                'wallet'      => 'MIND',
                                'amount'      => $row->amount,
                                'method'      => 'Token Settlement',
                                'type'        => 'Credit',
                                'status'      => 'Approved',
                                'description' => $row->amount . ' MIND Token settlement bonus for staking',
                            ]);
                        }

                        $row->update([
                            'status' => 0
                        ]);
                    }
                }
            }

            $this->info('Mind staking cron executed successfully.');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
