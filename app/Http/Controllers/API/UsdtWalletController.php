<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EliteSetting;
use App\Models\Transaction;
use App\Models\UsdtStakingHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsdtWalletController extends Controller
{
    public function joinElite(Request $request)
    {
        try {

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Check if user is eligible to join elite club
            if ($user->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not eligible'
                ], 403);
            }

            // Check if user is already an elite member
            if ($user->elite_club == 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Already Elite Member'
                ], 400);
            }


            // Get elite settings
            $setting = EliteSetting::first();

            if (!$setting) {
                return response()->json([
                    'status' => false,
                    'message' => 'Elite settings not found'
                ], 404);
            }

            $fee = $setting->mem_fee;

            // Check if user has sufficient balance
            $balance = Transaction::where('user_id', $user->id)
                ->where('status', 'Approved')
                ->where('wallet', 'USDT')
                ->sum('amount');

             // Check if balance is less than fee
            if ($balance < $fee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient Balance'
                ], 402);
            }

            DB::beginTransaction();

            // Deduct fee from user's USDT wallet
            Transaction::create([
                'user_id' => $user->id,
                'amount' => -$fee,
                'wallet' => 'USDT',
                'type' => 'Debit',
                'method' => 'Elite Membership',
                'status' => 'Approved',
                'description' => 'Elite membership purchased'
            ]);

            // Add staking entry for elite membership
            UsdtStakingHistory::create([
                'user_id' => $user->id,
                'amount' => $fee,
                'daily_bonus' => $setting->daily_bonus,
                'wallet' => 'USDT',
                'type' => 'Debit',
                'method' => 'Elite Membership',
                'status' => 'Approved',
                'description' => 'Elite staking activated'
            ]);

            // Update user's elite club status
            $user->elite_club = 1;
            $user->save();

            // Distribute bonuses to sponsors
            if ($user->sponsor) {

                $sponsor = User::find($user->sponsor)->first();

                if ($sponsor) {

                    $bonusPercent = ($setting->sponsor_bonus + $setting->lvl1) / 100;
                    $bonusAmount = $fee * $bonusPercent;

                    Transaction::create([
                        'user_id' => $sponsor->id,
                        'amount' => $bonusAmount,
                        'wallet' => 'USDT',
                        'type' => 'Credit',
                        'method' => 'Elite Bonus',
                        'status' => 'Approved',
                        'description' => 'Sponsor + Level 1 bonus from ' . $user->name
                    ]);

                    //  Check for level 2 sponsor and distribute bonus
                    if ($sponsor->sponsor) {

                        $lvl2 = User::find($sponsor->sponsor)->first();

                        if ($lvl2) {

                            $lvl2Amount = $fee * ($setting->lvl2 / 100);

                            Transaction::create([
                                'user_id' => $lvl2->id,
                                'amount' => $lvl2Amount,
                                'wallet' => 'USDT',
                                'type' => 'Credit',
                                'method' => 'Elite Level 2 Bonus',
                                'status' => 'Approved',
                                'description' => 'Level 2 elite bonus received from ' . $user->name
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Elite membership purchased successfully',
                'data' => [
                    'balance' => $balance - $fee
                ]
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
