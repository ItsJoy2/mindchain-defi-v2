<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PurchaseStaking;
use App\Models\Transaction;
use App\Models\AmbassadorHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MinddFastBonus extends Command
{
    protected $signature = 'mind:fast-bonus';
    protected $description = 'Fast Mind Daily Bonus Cron';

    public function handle()
    {
        try {

            $cutoffDate = Carbon::parse('2026-01-06');
            $today      = Carbon::today();

            $processed = 0;

            PurchaseStaking::where('status', 1)
                ->chunkById(100, function ($purchases) use ($cutoffDate, $today, &$processed) {

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

                            $purchaseDate = Carbon::parse($row->created_at);

                            // ✅ SAME DUEL DATE LOGIC
                            $startDate = $purchaseDate->lt($cutoffDate)
                                ? $cutoffDate
                                : $purchaseDate;

                            $totalDays = $startDate->diffInDays($today);

                            $duration = (int) $row->duration;
                            $received = (int) $row->received_days;

                            // =====================
                            // ACTIVE STAKING
                            // =====================
                            if ($received < $duration) {

                                Transaction::create([
                                    'user_id'     => $user->id,
                                    'wallet'      => 'MIND',
                                    'amount'      => $row->daily,
                                    'method'      => 'Daily Staking Bonus',
                                    'type'        => 'Credit',
                                    'status'      => 'Approved',
                                    'description' => $row->daily . ' MIND Daily Bonus',
                                ]);

                                // SPONSOR BONUS
                                if ($user->sponsor_id) {

                                    $sponsor = User::find($user->sponsor_id);

                                    if ($sponsor && $row->seller_bonus_rate > 0) {

                                        $sellerBonus = $row->daily * ($row->seller_bonus_rate / 100);

                                        Transaction::create([
                                            'user_id'     => $sponsor->id,
                                            'wallet'      => 'MIND',
                                            'amount'      => $sellerBonus,
                                            'method'      => 'Daily Seller Bonus',
                                            'type'        => 'Credit',
                                            'status'      => 'Approved',
                                            'description' => $sellerBonus . ' MIND Seller Bonus from ' . $user->user_name,
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

                                    if ($user->ambassador == 1) {

                                        AmbassadorHistory::create([
                                            'user_id'     => $user->id,
                                            'wallet'      => 'MIND',
                                            'amount'      => $row->amount,
                                            'method'      => 'Token Settlement',
                                            'type'        => 'Credit',
                                            'status'      => 'approved',
                                            'description' => $row->amount . ' MIND Settlement',
                                        ]);

                                    } else {

                                        Transaction::create([
                                            'user_id'     => $user->id,
                                            'wallet'      => 'MIND',
                                            'amount'      => $row->amount,
                                            'method'      => 'Token Settlement',
                                            'type'        => 'Credit',
                                            'status'      => 'Approved',
                                            'description' => $row->amount . ' MIND Settlement',
                                        ]);
                                    }

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
