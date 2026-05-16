<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseStaking;
use App\Models\Transaction;
use App\Models\User;

class MusdDailyStaking extends Command
{
    protected $signature = 'musd:staking-daily';
    protected $description = 'MUSD Daily Staking Cron';

    public function handle()
    {
        try {

            $purchases = PurchaseStaking::where('wallet', 'MUSD')
                ->where('status', 1)
                ->get();

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
                        'wallet'      => 'MUSD',
                        'amount'      => $row->daily,
                        'method'      => 'Daily Staking Bonus',
                        'type'        => 'Credit',
                        'status'      => 'Approved',
                        'description' => $row->daily . ' MUSD Daily Bonus for staking',
                    ]);

                    // SELLER/SPONSOR BONUS
                    $sponsor = $user->sponsor_id
                        ? User::find($user->sponsor_id)
                        : null;

                    if ($sponsor && $row->seller_bonus_rate > 0) {

                        $sellerBonus = $row->daily * ($row->seller_bonus_rate / 100);

                        Transaction::create([
                            'user_id'       => $sponsor->id,
                            'wallet'        => 'MUSD',
                            'amount'        => $sellerBonus,
                            'method'        => 'Daily Seller Bonus',
                            'type'          => 'Credit',
                            'status'        => 'Approved',
                            'received_from' => $user->id,
                            'description'   => $sellerBonus . ' MUSD Daily Seller Bonus from ' . $user->user_name,
                        ]);
                    }

                    // INCREMENT RECEIVED DAYS
                    $row->increment('received_days');
                }

                // SETTLEMENT (EXPIRE)
                else {

                    if ($row->status == 1) {

                        // RETURN STAKED AMOUNT
                        Transaction::create([
                            'user_id'     => $user->id,
                            'wallet'      => 'MUSD',
                            'amount'      => $row->amount,
                            'method'      => 'Token Settlement',
                            'type'        => 'Credit',
                            'status'      => 'Approved',
                            'description' => $row->amount . ' MUSD settlement bonus for staking',
                        ]);

                        // CLOSE STAKING
                        $row->update([
                            'status' => 0
                        ]);
                    }
                }
            }

            $this->info('MUSD staking cron executed successfully.');

        } catch (\Exception $e) {

            $this->error($e->getMessage());
        }
    }
}
