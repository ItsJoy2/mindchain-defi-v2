<?php

namespace App\Console\Commands;

use App\Models\EliteStaking;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EliteV2FastBonus extends Command
{
    protected $signature = 'elitev2:fast-bonus';
    protected $description = 'Fast Elite V2 daily bonus generator';

    // public function handle()
    // {
    //     try {

    //         $cutoffDate = Carbon::parse('2026-01-06');
    //         $today      = Carbon::today();

    //         $processed = 0;

    //         EliteStaking::where('wallet', 'MUSD')
    //             ->where('status', 1)
    //             ->chunkById(50, function ($stakings) use ($cutoffDate, $today, &$processed) {

    //                 $batchInsert = [];

    //                 foreach ($stakings as $staking) {

    //                     DB::beginTransaction();

    //                     try {

    //                         $purchaseDate = Carbon::parse($staking->created_at);

    //                         // 🔥 dual logic start date
    //                         $startDate = $purchaseDate->lt($cutoffDate)
    //                             ? $cutoffDate
    //                             : $purchaseDate;

    //                         $totalDays = $startDate->diffInDays($today);

    //                         $dailyBonus = $staking->daily_bonus;
    //                         $amount     = $staking->amount;
    //                         $duration   = $staking->duration;

    //                         for ($i = 0; $i <= $totalDays; $i++) {

    //                             $date = (clone $startDate)->addDays($i);

    //                             $daysFromPurchase = $purchaseDate->diffInDays($date);

    //                             // duration cross → 20% APY
    //                             if ($daysFromPurchase > $duration) {
    //                                 $dailyBonus = ($amount * 20) / (100 * $duration);
    //                             }

    //                             $batchInsert[] = [
    //                                 'user_id'     => $staking->user_id,
    //                                 'amount'      => $dailyBonus,
    //                                 'wallet'      => $staking->wallet,
    //                                 'type'        => 'Credit',
    //                                 'method'      => 'Daily Elite V2 Bonus',
    //                                 'status'      => 'Approved',
    //                                 'created_at'  => $date->format('Y-m-d') . ' ' . now()->format('H:i:s'),
    //                                 'updated_at'  => now(),
    //                                 'description' => '$' . $staking->daily_bonus . ' Daily Bonus for Elite V2 Membership'
    //                             ];

    //                             // 🔥 safe batch limit
    //                             if (count($batchInsert) >= 500) {
    //                                 DB::table('transactions')->insert($batchInsert);
    //                                 $batchInsert = [];
    //                             }
    //                         }

    //                         $staking->increment('received_days');

    //                         DB::commit();
    //                         $processed++;

    //                     } catch (\Exception $e) {
    //                         DB::rollBack();
    //                         continue;
    //                     }
    //                 }

    //                 // remaining insert
    //                 if (!empty($batchInsert)) {
    //                     DB::table('transactions')->insert($batchInsert);
    //                 }
    //             });

    //         return Command::SUCCESS;

    //     } catch (\Exception $e) {

    //         $this->error($e->getMessage());

    //         return Command::FAILURE;
    //     }
    // }
}
