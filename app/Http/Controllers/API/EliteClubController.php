<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EliteSetting;
use App\Models\EliteV2Setting;
use App\Models\EliteV2StakingHistory;
use App\Models\Transaction;
use App\Models\UsdtStakingHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EliteClubController extends Controller
{

    public function index()
    {
        try {

            $elite = EliteSetting::first();
            $eliteV2 = EliteV2Setting::first();

            return response()->json([
                'status' => true,
                'message' => 'Elite settings fetched successfully',

                'data' => [

                    'elite_membership_fee' => $elite->mem_fee ?? 0,
                    'elite_v2_membership_fee' => $eliteV2->mem_fee ?? 0,

                    // 'elite' => [
                    //     'mem_fee'        => $elite->mem_fee ?? 0,
                    //     'daily_bonus'    => $elite->daily_bonus ?? 0,
                    //     'duration'       => $elite->duration ?? 0,
                    //     'sponsor_bonus'  => $elite->sponsor_bonus ?? 0,
                    //     'lvl1'           => $elite->lvl1 ?? 0,
                    //     'lvl2'           => $elite->lvl2 ?? 0,
                    //     'status'         => $elite->status ?? 0,
                    // ],

                    // 'elite_v2' => [
                    //     'mem_fee'        => $eliteV2->mem_fee ?? 0,
                    //     'daily_bonus'    => $eliteV2->daily_bonus ?? 0,
                    //     'duration'       => $eliteV2->duration ?? 0,
                    //     'sponsor_bonus'  => $eliteV2->sponsor_bonus ?? 0,
                    //     'lvl1'           => $eliteV2->lvl1 ?? 0,
                    //     'lvl2'           => $eliteV2->lvl2 ?? 0,
                    //     'status'         => $eliteV2->status ?? 0,
                    // ]
                ]

            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
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
                'duration' => $setting->duration,
                'method' => 'Elite Membership',
                'status' => 'Approved',
                'description' => 'Elite staking activated'
            ]);

            // Update user's elite club status
            $user->elite_club = 1;
            $user->save();

            // Distribute bonuses to sponsors
            if ($user->sponsor) {

                $sponsor = $user->sponsor;

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

                        $lvl2 = $sponsor->sponsor;

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


    public function joinEliteV2(Request $request)
    {
        try {

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Check eligibility
            if ($user->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not eligible'
                ], 403);
            }

            // Already joined check
            if ($user->is_elite_v2 == 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Already Elite V2 Member'
                ], 400);
            }


            $setting = EliteV2Setting::first();

            if (!$setting) {
                return response()->json([
                    'status' => false,
                    'message' => 'Elite V2 settings not found'
                ], 404);
            }

            $fee = $setting->mem_fee;


            $balance = Transaction::where('user_id', $user->id)
                ->whereIn('status', ['Approved', 'Pending'])
                ->where('wallet', 'MUSD')
                ->sum('amount');

            if ($balance < $fee) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient MUSD Balance'
                ], 402);
            }

            DB::beginTransaction();


            Transaction::create([
                'user_id' => $user->id,
                'amount' => -$fee,
                'wallet' => 'MUSD',
                'type' => 'Debit',
                'method' => 'Elite V2 Membership',
                'status' => 'Approved',
                'description' => 'Elite V2 membership purchased'
            ]);

            EliteV2StakingHistory::create([
                'user_id' => $user->id,
                'amount' => $fee,
                'daily_bonus' => $setting->daily_bonus,
                'wallet' => 'MUSD',
                'type' => 'Debit',
                'duration' => $setting->duration,
                'method' => 'Elite V2 Membership',
                'status' => 'Approved',
                'description' => 'Elite V2 staking activated'
            ]);

            $user->is_elite_v2 = 1;
            $user->save();


            if ($user->sponsor) {

                $sponsor = $user->sponsor;

                if ($sponsor) {

                    $bonusPercent = ($setting->sponsor_bonus + $setting->lvl1) / 100;
                    $bonusAmount = $fee * $bonusPercent;

                    Transaction::create([
                        'user_id' => $sponsor->id,
                        'amount' => $bonusAmount,
                        'wallet' => 'MUSD',
                        'type' => 'Credit',
                        'method' => 'Elite V2 Bonus',
                        'status' => 'Approved',
                        'description' => 'Sponsor + Level 1 bonus from ' . ($user->name ?? 'User')
                    ]);

                    if ($sponsor->sponsor) {

                        $lvl2 = $sponsor->sponsor;

                        if ($lvl2) {

                            $lvl2Amount = $fee * ($setting->lvl2 / 100);

                            Transaction::create([
                                'user_id' => $lvl2->id,
                                'amount' => $lvl2Amount,
                                'wallet' => 'MUSD',
                                'type' => 'Credit',
                                'method' => 'Elite V2 Level 2 Bonus',
                                'status' => 'Approved',
                                'description' => 'Level 2 elite V2 bonus received from ' . ($user->name ?? 'User')
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Elite V2 membership purchased successfully',
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
