<?php
namespace App\Console\Commands;

use App\Models\EliteStaking;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EliteFastBonus extends Command
{
    protected $signature = 'elite:fast-bonus';
    protected $description = 'Fast daily elite staking bonus generator';

    // public function handle()
    // {
    //     try {

    //         $cutoffDate = Carbon::parse('2026-01-06');
    //         $today      = Carbon::today();

    //         $processed = 0;

    //         EliteStaking::where('wallet', 'USDT')
    //             ->where('status', 1)
    //             ->chunkById(50, function ($stakings) use ($cutoffDate, $today, &$processed) {

    //                 $batchInsert = [];

    //                 foreach ($stakings as $staking) {

    //                     DB::beginTransaction();

    //                     try {

    //                         $purchaseDate = Carbon::parse($staking->created_at);

    //                         // ✅ same dual date logic
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

    //                             // duration cross → 15% APY
    //                             if ($daysFromPurchase > $duration) {
    //                                 $dailyBonus = ($amount * 15) / (100 * $duration);
    //                             }

    //                             $batchInsert[] = [
    //                                 'user_id'     => $staking->user_id,
    //                                 'amount'      => $dailyBonus,
    //                                 'wallet'      => $staking->wallet,
    //                                 'type'        => 'Credit',
    //                                 'method'      => 'Daily Elite Bonus',
    //                                 'status'      => 'Approved',
    //                                 'created_at'  => $date->format('Y-m-d') . ' ' . now()->format('H:i:s'),
    //                                 'updated_at'  => now(),
    //                                 'description' => 'Daily Elite Bonus'
    //                             ];

    //                             // 🔥 safe batch insert limit
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
