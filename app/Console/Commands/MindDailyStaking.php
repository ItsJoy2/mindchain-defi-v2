<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MindPurchaseStake;
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

            $purchases = MindPurchaseStake::where('status', 1)->get();

            foreach ($purchases as $row) {

                $user = User::find($row->user_id);

                if (!$user) {
                    continue;
                }

                // ACTIVE STAKING
                if ($row->received_days < $row->duration) {

                    // DAILY BONUS
                    Transaction::create([
                        'user_id'     => $user->id,
                        'wallet'      => 'MIND',
                        'amount'      => $row->daily,
                        'method'      => 'Daily Staking Bonus',
                        'type'        => 'Credit',
                        'status'      => 'Approved',
                        'description' => $row->daily . ' MIND Token Daily Bonus for purchasing Staking Package',
                    ]);

                    // SPONSOR BONUS
                    $sponsor = $user->sponsor
                        ? User::find($user->sponsor)
                        : null;

                    if ($sponsor && $row->seller_bonus_rate > 0) {

                        $sellerBonus = $row->daily * ($row->seller_bonus_rate / 100);

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

                    // RECEIVED DAYS UPDATE
                    $row->increment('received_days');
                }

                // STAKING COMPLETE
                else {

                    if ($row->status == 1) {

                        // Ambassador Settlement
                        if ($user->ambassador == 1) {

                            AmbassadorHistory::create([
                                'user_id'     => $user->id,
                                'wallet'      => 'MIND',
                                'amount'      => $row->amount,
                                'method'      => 'Token Settlement',
                                'type'        => 'Credit',
                                'status'      => 'Approved',
                                'description' => $row->amount . ' MIND Token settlement bonus for staking',
                            ]);

                        }

                        // Normal User Settlement
                        else {

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

                        // CLOSE STAKING
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
