<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseStaking;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MusdFastBonus extends Command
{
    protected $signature = 'musd:fast-staking';
    protected $description = 'Fast MUSD Daily Staking Cron';

    public function handle()
    {
        try {

            $processed = 0;

            PurchaseStaking::where('wallet', 'MUSD')
                ->where('status', 1)
                ->chunkById(100, function ($purchases) use (&$processed) {

                    foreach ($purchases as $row) {

                        DB::beginTransaction();

                        try {

                            if (!$row || !$row->user_id) {
                                DB::rollBack();
                                continue;
                            }

                            $user = User::find($row->user_id);

                            if (!$user) {
                                DB::rollBack();
                                continue;
                            }

                            $duration = (int) $row->duration;
                            $received = (int) $row->received_days;

                            // =====================
                            // ACTIVE STAKING
                            // =====================
                            if ($received < $duration) {

                                Transaction::create([
                                    'user_id'     => $user->id,
                                    'wallet'      => 'MUSD',
                                    'amount'      => $row->daily,
                                    'method'      => 'Daily Staking Bonus',
                                    'type'        => 'Credit',
                                    'status'      => 'Approved',
                                    'description' => $row->daily . ' MUSD Daily Bonus',
                                ]);

                                // SPONSOR BONUS
                                if ($user->sponsor_id) {

                                    $sponsor = User::find($user->sponsor_id);

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
                                            'description'   => $sellerBonus . ' MUSD Seller Bonus',
                                        ]);
                                    }
                                }

                                $row->increment('received_days');
                            }

                            // =====================
                            // SETTLEMENT (EXPIRE)
                            // =====================
                            else {

                                if ($row->status == 1) {

                                    Transaction::create([
                                        'user_id'     => $user->id,
                                        'wallet'      => 'MUSD',
                                        'amount'      => $row->amount,
                                        'method'      => 'Token Settlement',
                                        'type'        => 'Credit',
                                        'status'      => 'Approved',
                                        'description' => $row->amount . ' MUSD Settlement',
                                    ]);

                                    $row->update([
                                        'status' => 0
                                    ]);
                                }
                            }

                            DB::commit();
                            $processed++;

                        } catch (\Exception $e) {
                            DB::rollBack();
                            continue;
                        }
                    }
                });

            return Command::SUCCESS;

        } catch (\Exception $e) {

            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
