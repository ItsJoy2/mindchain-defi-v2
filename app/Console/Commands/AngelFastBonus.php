<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AngelStaking;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AngelFastBonus extends Command
{
    protected $signature = 'angel:fast-bonus';
    protected $description = 'Fast and safe Angel bonus generator';

    // public function handle()
    // {
    //     try {

    //         $cutoffDate = Carbon::parse('2026-01-06');
    //         $today      = Carbon::today();

    //         $processed = 0;

    //         AngelStaking::where('status', 1)
    //             ->chunkById(50, function ($stakings) use ($cutoffDate, $today, &$processed) {

    //                 $batchInsert = [];

    //                 foreach ($stakings as $staking) {

    //                     $purchaseDate = Carbon::parse($staking->created_at);

    //                     // ✅ dual logic start date
    //                     $startDate = $purchaseDate->lt($cutoffDate)
    //                         ? $cutoffDate
    //                         : $purchaseDate;

    //                     $totalDays = $startDate->diffInDays($today);

    //                     $dailyBonus = $staking->daily_bonus;
    //                     $amount     = $staking->amount;
    //                     $duration   = $staking->duration;

    //                     for ($i = 0; $i <= $totalDays; $i++) {

    //                         $date = (clone $startDate)->addDays($i);

    //                         $daysFromPurchase = $purchaseDate->diffInDays($date);

    //                         if ($daysFromPurchase > $duration) {
    //                             $dailyBonus = ($amount * 25) / (100 * $duration);
    //                         }

    //                         $batchInsert[] = [
    //                             'user_id'     => $staking->user_id,
    //                             'amount'      => $dailyBonus,
    //                             'wallet'      => 'MUSD',
    //                             'type'        => 'Credit',
    //                             'method'      => 'Daily Angel Bonus',
    //                             'status'      => 'Approved',
    //                             'created_at'  => $date->format('Y-m-d') . ' ' . now()->format('H:i:s'),
    //                             'updated_at'  => now(),
    //                             'description' => $staking->daily_bonus . ' Daily Bonus for Angel Membership'
    //                         ];

    //                         // 🔥 SAFE LIMIT (prevent MySQL crash)
    //                         if (count($batchInsert) >= 500) {
    //                             DB::table('transactions')->insert($batchInsert);
    //                             $batchInsert = [];
    //                         }
    //                     }

    //                     $processed++;
    //                 }

    //                 // insert remaining
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
