<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorHistory;
use App\Models\LevelSetting;
use App\Models\MindPurchaseStake;
use App\Models\MindStakingSetting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\number;

class MindWalletController extends Controller
{

    // Get staking plans and settings
    public function mindStaking()
    {
        try {

            $user = auth()->user();

            $staking = MindStakingSetting::first();

            if (!$staking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Staking settings not found',
                    'data' => null
                ]);
            }

            // USER TOTAL STAKED
            $myStaked = 0;

            // USER TOTAL REWARDS
            $totalRewards = 0;

            // NETWORK APR (average)
            $networkApr = number_format(
                (
                    $staking->days_180 +
                    $staking->days_365 +
                    $staking->days_730 +
                    $staking->days_1825
                ) / 4,
                2
            );

            if ($user) {

                $myStaked = MindPurchaseStake::where('user_id', $user->id)
                    ->where('status', 1)
                    ->sum('amount');

                $totalRewards = MindPurchaseStake::where('user_id', $user->id)
                    ->sum('total_value');
            }

            $plans = [
                // [
                //     'title' => '90 Days',
                //     'days'  => 90,
                //     'apy'   => number_format($staking->days_90, 2)
                // ],
                [
                    'title' => '180 Days',
                    'days'  => 180,
                    'apy'   => number_format($staking->days_180, 2)
                ],
                [
                    'title' => '365 Days',
                    'days'  => 365,
                    'apy'   => number_format($staking->days_365, 2)
                ],
                [
                    'title' => '730 Days',
                    'days'  => 730,
                    'apy'   => number_format($staking->days_730, 2)
                ],
                [
                    'title' => '1825 Days',
                    'days'  => 1825,
                    'apy'   => number_format($staking->days_1825, 2)
                ],
            ];

            return response()->json([
                'status' => true,
                'message' => 'MIND Staking data retrieved successfully',
                'data' => [
                     'network_apr' => $networkApr . '%',
                    'your_staked' => number_format($myStaked, 2),
                    'total_rewards' => number_format($totalRewards, 2),
                    'min_staking' => $staking->min_staking,
                    'max_staking' => $staking->max_staking,
                    'plans' => $plans
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => null
            ]);
        }
    }

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
            $purchase = MindPurchaseStake::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'duration' =>$duration,
                'received_days' => 0,
                'apy_value' => $apy_value,
                'total_value' => number_format($total_value, 5),
                'daily' => number_format($daily, 5),
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
                    'daily' => number_format($purchase->daily, 4),
                    'total_value' => number_format($purchase->total_value, 4),
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
            $purchase = MindPurchaseStake::create([
                'user_id' => $receiver->id,
                'amount' => $amount,
                'duration' => $duration,
                'received_days' => 0,
                'apy_value' => $apy_value,
                'daily' => number_format($daily, 5),
                'seller_bonus_rate' => $staking->seller_bonus,
                'total_value' => number_format($total_value, 5),
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
                    'daily' => number_format($daily, 4),
                    'total_value' => number_format($total_value, 4)
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

    // MIND staking history
    public function mindStakingHistory(Request $request)
    {
        try {

            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $perPage = (int) $request->get('per_page', 10);

            $histories = MindPurchaseStake::where('user_id', $user->id)
                ->select([
                    'id',
                    'amount',
                    'duration',
                    'received_days',
                    'daily',
                    'status',
                    'created_at'
                ])
                ->orderByDesc('id')
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Mind staking history retrieved successfully',
                'data' => collect($histories->items())->map(function ($row) {

                    return [
                        'id' => $row->id,
                        'amount' => (float) $row->amount,
                        'duration' => (int) $row->duration,
                        'received_days' => (int) $row->received_days,
                        'daily' => (float) $row->daily,
                        'status' => $row->status == 1 ? 'Running' : 'Expired',
                        'created_at' => $row->created_at->format('d M Y'),
                    ];
                }),

                'pagination' => [
                    'page' => $histories->currentPage(),
                    'per_page' => $histories->perPage(),
                    'total' => $histories->total(),
                ]

            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
