<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorHistory;
use App\Models\LevelSetting;
use App\Models\PurchaseStaking;
use App\Models\MindStakingSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\number;

class MindWalletController extends Controller
{
    // Store staking purchase
    public function mindStakingStore(Request $request)
    {
        try {

            $user = auth()->user();

            if (!$user || $user->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not eligible'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'duration' => 'required|integer|in:180,365,730,1825',
                'amount' => 'required|numeric|min:1',
                'wallet' => 'required|in:mind,ambassador'
            ]);

            if ($validator->fails()) {

                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $amount = (float) $request->amount;
            $wallet = $request->wallet;

            $staking = MindStakingSetting::first();

            if (!$staking) {

                return response()->json([
                    'status' => false,
                    'message' => 'Staking settings not found'
                ], 404);
            }


            if ($amount < $staking->min_staking) {

                return response()->json([
                    'status' => false,
                    'message' => "Minimum staking is {$staking->min_staking}"
                ], 400);
            }

            if ($amount > $staking->max_staking) {

                return response()->json([
                    'status' => false,
                    'message' => "Maximum staking is {$staking->max_staking}"
                ], 400);
            }

            $duration = (int) $request->duration;

            $plan = match ($duration) {

                // 90 => [
                //     'days' => 90,
                //     'apy' => $staking->days_90
                // ],

                180 => [
                    'days' => 180,
                    'apy' => $staking->days_180
                ],

                365 => [
                    'days' => 365,
                    'apy' => $staking->days_365
                ],

                730 => [
                    'days' => 730,
                    'apy' => $staking->days_730
                ],

                1825 => [
                    'days' => 1825,
                    'apy' => $staking->days_1825
                ],
            };

            $days = $plan['days'];
            $apy = (float) $plan['apy'];


            $apy_value = ($amount * $apy) / 100;

            $daily = $apy_value / 365;

            $total_value = $daily * $days;

            // Check balance and create transaction
            if ($wallet == "mind") {

                $balance = Transaction::where('user_id', $user->id)
                    ->where('wallet', 'MIND')
                    ->whereIn('status', ['Approved', 'Pending'])
                    ->where('method', '!=', ['Kids Program Membership', 'MIND Marge Staking Received'])
                    ->sum('amount');

                if ($balance < $amount) {

                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient MIND balance'
                    ], 400);
                }

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet' => 'MIND',
                    'amount' => -$amount,
                    'method' => 'Staking Purchase',
                    'type' => 'Debit',
                    'status' => 'Approved',
                    'description' => 'Mind staking purchase'
                ]);

            } else {

                $balance = AmbassadorHistory::where('user_id', $user->id)
                    ->sum('amount');

                if ($balance < $amount) {

                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient Ambassador balance'
                    ], 400);
                }

                AmbassadorHistory::create([
                    'user_id' => $user->id,
                    'wallet' => 'MIND',
                    'amount' => -$amount,
                    'method' => 'Staking Purchase',
                    'type' => 'Debit',
                    'status' => 'Approved',
                    'description' => 'Ambassador staking purchase'
                ]);
            }

            // Create staking purchase record
            $purchase = PurchaseStaking::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'duration' =>$duration,
                'received_days' => 0,
                'apy_value' => $apy_value,
                'total_value' => $total_value,
                'daily' => $daily,
                'seller_bonus_rate' => $staking->seller_bonus,
                'status' => 1
            ]);

            // Affiliate Bonus
            $sponsor = $user->sponsor;

            if ($wallet == "mind" && $sponsor) {

                $rate = match ($days) {

                    90 => $staking->days_90_af,
                    180 => $staking->days_180_af,
                    365 => $staking->days_365_af,
                    730 => $staking->days_730_af,

                    default => $staking->days_1825_af
                };

                Transaction::create([
                    'user_id' => $sponsor->id,
                    'wallet' => 'MIND',
                    'amount' => ($amount * $rate) / 100,
                    'method' => 'Affiliate Bonus',
                    'type' => 'Credit',
                    'status' => 'Approved',
                    'description' => 'Affiliate bonus from ' . $user->user_name
                ]);
            }


            // Level Bonus
            $g_set = LevelSetting::first();

            if ($g_set && $g_set->status == 1 && $sponsor) {

                $current = $sponsor;

                for ($i = 1; $i <= 5; $i++) {

                    if (!$current) {
                        break;
                    }

                    $rate = $g_set->{'lvl_' . $i};

                    if ($current->ambassador == 1) {

                        Transaction::create([
                            'user_id' => $current->id,
                            'wallet' => 'MIND',
                            'amount' => ($amount * $rate) / 100,
                            'method' => 'Level Bonus',
                            'type' => 'Credit',
                            'status' => 'Approved',
                            'description' => "Level {$i} bonus from {$user->user_name}"
                        ]);
                    }

                    $current = User::find($current->sponsor);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'MIND Staking successfully Staked',
                'data' => [
                    'amount' => $purchase->amount,
                    'duration' => $purchase->duration,
                    'apy' => $apy,
                    'apy_value' => $purchase->apy_value,
                    'daily' => number_format($purchase->daily, 2),
                    'total_value' => number_format($purchase->total_value, 2),
                ]
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Staking Marge between users
    public function mindStakingMarge(Request $request)
    {
        try {

            $user = auth()->user();

            if (!$user || $user->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not eligible'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'receiver_user_name' => 'required|exists:users,user_name',
                'amount' => 'required|numeric|min:1',
                'wallet' => 'required|in:mind,ambassador',
                'duration' => 'required|integer|in:180,365,730,1825'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $amount = (float) $request->amount;
            $wallet = $request->wallet;

            $receiver = User::where('user_name', $request->receiver_user_name)->first();

            if (!$receiver) {
                return response()->json([
                    'status' => false,
                    'message' => 'Receiver not found'
                ], 404);
            }

            $staking = MindStakingSetting::first();

            if (!$staking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Staking settings not found'
                ], 404);
            }

            //  WALLET CHECK (sender)
            if ($wallet == "mind") {

                $balance = Transaction::where('user_id', $user->id)
                    ->where('wallet', 'MIND')
                    ->whereIn('status', ['Approved', 'Pending'])
                    ->whereNotIn('method', ['Kids Program Membership', 'MIND Marge Staking Received'])
                    ->sum('amount');

                if ($balance < $amount) {

                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient MIND balance'
                    ], 400);
                }

                Transaction::create([
                    'user_id' => $user->id,
                    'wallet' => 'MIND',
                    'amount' => -$amount,
                    'method' => 'MIND Marge Staking Sent',
                    'type' => 'Debit',
                    'status' => 'Approved',
                    'description' => "MIND staking sent to {$receiver->user_name}"
                ]);

            } else {

                $balance = AmbassadorHistory::where('user_id', $user->id)
                    ->sum('amount');

                if ($balance < $amount) {

                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient Ambassador balance'
                    ], 400);
                }

                AmbassadorHistory::create([
                    'user_id' => $user->id,
                    'wallet' => 'MIND',
                    'amount' => -$amount,
                    'method' => 'MIND Marge Staking Sent',
                    'type' => 'Debit',
                    'status' => 'Approved',
                    'description' => "Ambassador staking sent to {$receiver->user_name}"
                ]);
            }

            //  PLAN
            $duration = (int) $request->duration;

            $plan = match ($duration) {

                // 90 => [
                //     'days' => 90,
                //     'apy' => $staking->days_90
                // ],

                180 => [
                    'days' => 180,
                    'apy' => $staking->days_180
                ],

                365 => [
                    'days' => 365,
                    'apy' => $staking->days_365
                ],

                730 => [
                    'days' => 730,
                    'apy' => $staking->days_730
                ],

                1825 => [
                    'days' => 1825,
                    'apy' => $staking->days_1825
                ],
            };

            $days = $plan['days'];
            $apy = (float) $plan['apy'];

            $apy_value = ($amount * $apy) / 100;
            $daily = $apy_value / 365;
            $total_value = $daily * $days;

            //  CREATE STAKE FOR RECEIVER
            $purchase = PurchaseStaking::create([
                'user_id' => $receiver->id,
                'amount' => $amount,
                'duration' => $duration,
                'received_days' => 0,
                'apy_value' => $apy_value,
                'daily' =>$daily,
                'seller_bonus_rate' => $staking->seller_bonus,
                'total_value' => $total_value,
                'status' => 1
            ]);

            //  RECEIVER HISTORY
            Transaction::create([
                'user_id' => $receiver->id,
                'wallet' => 'MIND',
                'amount' => $amount,
                'method' => 'MIND Marge Staking Received',
                'type' => 'Credit',
                'status' => 'Approved',
                'description' => "Received MIND staking from {$user->user_name}"
            ]);

            //  SPONSOR LOGIC
            $sponsor = $receiver->sponsor;

            if ($wallet == "mind" && $sponsor) {

                $rate = match ($days) {
                    90 => $staking->days_90_af,
                    180 => $staking->days_180_af,
                    365 => $staking->days_365_af,
                    730 => $staking->days_730_af,
                    default => $staking->days_1825_af
                };

                Transaction::create([
                    'user_id' => $sponsor->id,
                    'wallet' => 'MIND',
                    'amount' => ($amount * $rate) / 100,
                    'method' => 'Affiliate Bonus',
                    'type' => 'Credit',
                    'status' => 'Approved',
                    'description' => "Affiliate from {$receiver->user_name}"
                ]);
            }

            //  LEVEL BONUS
            $g_set = LevelSetting::first();

            if ($g_set && $g_set->status == 1 && $sponsor) {

                $current = $sponsor;

                for ($i = 1; $i <= 5; $i++) {

                    if (!$current) break;

                    $rate = $g_set->{'lvl_' . $i};

                    if ($current->ambassador == 1) {

                        Transaction::create([
                            'user_id' => $current->id,
                            'wallet' => 'MIND',
                            'amount' => ($amount * $rate) / 100,
                            'method' => 'Level Bonus',
                            'type' => 'Credit',
                            'status' => 'Approved',
                            'description' => "Level {$i} bonus from {$receiver->user_name}"
                        ]);
                    }

                    $current = User::find($current->sponsor);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'MIND Marge Staking successfully',
                'data' => [
                    'from' => $user->user_name,
                    'to' => $receiver->user_name,
                    'amount' => $amount,
                    'apy_value' => $apy_value,
                    'daily' => number_format($daily, 2),
                    'total_value' => number_format($total_value, 2)
                ]
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
