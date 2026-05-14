<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BmindStakingSetting;
use App\Models\PurchaseStaking;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BmindWalletController extends Controller
{

    public function bmindStaking()
    {
        try {

            $user = auth()->user();

            $setting = BmindStakingSetting::first();

            if (!$setting) {
                return response()->json([
                    'status' => false,
                    'message' => 'Staking settings not found',
                    'data' => null
                ]);
            }

            $myStaked = 0;
            $totalRewards = 0;

            if ($user) {

                $myStaked = PurchaseStaking::where('user_id', $user->id)
                    ->where('wallet', 'BMIND')
                    ->where('status', 1)
                    ->sum('amount');

                $totalRewards = PurchaseStaking::where('user_id', $user->id)
                    ->where('wallet', 'BMIND')
                    ->where('status', 1)
                    ->sum('total_value');
            }


            $networkApr = number_format(
                (
                    $setting->days_180 +
                    $setting->days_365 +
                    $setting->days_730
                ) / 3,
                2
            );

            $plans = [

                [
                    'title' => '180 Days',
                    'days'  => 180,
                    'apy'   => number_format($setting->days_180, 2)
                ],
                [
                    'title' => '365 Days',
                    'days'  => 365,
                    'apy'   => number_format($setting->days_365, 2)
                ],
                [
                    'title' => '730 Days',
                    'days'  => 730,
                    'apy'   => number_format($setting->days_730, 2)
                ],
            ];

            return response()->json([
                'status' => true,
                'message' => 'BMIND staking data retrieved successfully',
                'data' => [
                    'network_apr'   => $networkApr . '%',
                    'your_staked'    => number_format($myStaked, 2),
                    'total_rewards'  => number_format($totalRewards, 2),
                    'min_staking'    => $setting->min_staking,
                    'max_staking'    => $setting->max_staking,
                    'plans'          => $plans
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ]);
        }
    }
    public function storeBmindStaking(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'amount'   => 'required|numeric|min:1',
                'duration' => 'required|in:180,365,730',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            if ($user->status == 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not eligible'
                ], 403);
            }

            $setting = BmindStakingSetting::first();

            if (!$setting) {
                return response()->json([
                    'status' => false,
                    'message' => 'Staking settings not found'
                ], 404);
            }

            if ($request->amount < $setting->min_staking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Minimum staking is ' . $setting->min_staking
                ], 422);
            }

            if ($request->amount > $setting->max_staking) {
                return response()->json([
                    'status' => false,
                    'message' => 'Maximum staking is ' . $setting->max_staking
                ], 422);
            }

            $walletService = new WalletService();

            if (!$walletService->hasBalance($user->id, 'BMIND', $request->amount)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient BMIND balance'
                ], 422);
            }

            $profitPercent = 0;
            $affiliatePercent = 0;

            if ($request->duration == 180) {
                $profitPercent = $setting->days_180;
                $affiliatePercent = $setting->days_180_af;
            } elseif ($request->duration == 365) {
                $profitPercent = $setting->days_365;
                $affiliatePercent = $setting->days_365_af;
            } elseif ($request->duration == 730) {
                $profitPercent = $setting->days_730;
                $affiliatePercent = $setting->days_730_af;
            }

            $dailyProfit = (($request->amount * $profitPercent) / 100) / 365;
            $totalProfit  = $dailyProfit * $request->duration;

            Transaction::create([
                'user_id'     => $user->id,
                'wallet'      => 'BMIND',
                'amount'      => -$request->amount,
                'method'      => 'BMIND Staking',
                'type'        => 'Debit',
                'status'      => 'Approved',
                'description' => -$request->amount . 'BMIND staking purchase',
            ]);

            $staking = PurchaseStaking::create([
                'user_id'             => $user->id,
                'wallet'              => 'BMIND',
                'amount'              => $request->amount,
                'duration'            => $request->duration,
                'received_days'       => 0,
                'apy_value'           => $profitPercent,
                'total_value'         => $totalProfit,
                'daily'               => $dailyProfit,
                'seller_bonus_rate'   => $setting->seller_bonus ?? 5,
                'status'              => 1,
            ]);

            if ($user->sponsor_id) {

                $sponsor = User::find($user->sponsor_id);

                if ($sponsor) {

                    $affiliateBonus = ($request->amount * $affiliatePercent) / 100;

                    Transaction::create([
                        'user_id'       => $sponsor->id,
                        'wallet'        => 'BMIND',
                        'amount'        => $affiliateBonus,
                        'method'        => 'Affiliate Bonus',
                        'type'          => 'Credit',
                        'status'        => 'Approved',
                        'received_from' => $user->id,
                        'description'   => 'Affiliate bonus from ' . $user->user_name,
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'BMIND staking successful',
                'data' => [
                    'id'           => $staking->id,
                    'amount'       => $staking->amount,
                    'duration'     => $staking->duration,
                    'daily_profit' => $dailyProfit,
                    'total_profit' => $totalProfit,
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
