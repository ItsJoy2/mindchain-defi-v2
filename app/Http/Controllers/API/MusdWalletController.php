<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MusdStakingSetting;
use App\Models\PurchaseStaking;
use App\Models\Transaction;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MusdWalletController extends Controller
{
    public function storeMusdStaking(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'amount'   => 'required|numeric|min:1',
                'duration' => 'required|in:365,730,1825',
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

            $setting = MusdStakingSetting::first();

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

            if (!$walletService->hasBalance($user->id, 'MUSD', $request->amount)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient MUSD balance'
                ], 422);
            }

            $profitPercent = 0;
            $affiliatePercent = 0;

            if ($request->duration == 365) {
                $profitPercent = $setting->days_365;
                $affiliatePercent = $setting->days_365_af;
            } elseif ($request->duration == 730) {
                $profitPercent = $setting->days_730;
                $affiliatePercent = $setting->days_730_af;
            } elseif ($request->duration == 1825) {
                $profitPercent = $setting->days_1825;
                $affiliatePercent = $setting->days_1825_af;
            }

            $dailyProfit = (($request->amount * $profitPercent) / 100) / 365;
            $totalProfit  = $dailyProfit * $request->duration;

            Transaction::create([
                'user_id'     => $user->id,
                'wallet'      => 'MUSD',
                'amount'      => -$request->amount,
                'method'      => 'MUSD Staking',
                'type'        => 'Debit',
                'status'      => 'Approved',
                'description' => $request->amount . ' MUSD staking purchase',
            ]);

            $staking = PurchaseStaking::create([
                'user_id'             => $user->id,
                'wallet'              => 'MUSD',
                'amount'              => $request->amount,
                'duration'            => $request->duration,
                'received_days'       => 0,
                'apy_value'           => $profitPercent,
                'total_value'         => $totalProfit,
                'daily'               => $dailyProfit,
                'seller_bonus_rate'   => $setting->seller_bonus ?? 3,
                'status'              => 1,
            ]);

            // Affiliate Bonus
            if ($user->sponsor_id) {

                $sponsor = User::find($user->sponsor_id);

                if ($sponsor) {

                    $affiliateBonus = ($request->amount * $affiliatePercent) / 100;

                    Transaction::create([
                        'user_id'       => $sponsor->id,
                        'wallet'        => 'MUSD',
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
                'message' => 'MUSD staking successful',
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
