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
    public function storeBmindStaking(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'amount'   => 'required|numeric|min:1',
                'duration' => 'required|in:180,365,730,1825',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation error',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            if ($user->status == 0) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You are not eligible'
                ], 403);
            }

            $setting = BmindStakingSetting::first();

            if (!$setting) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Staking settings not found'
                ], 404);
            }

            if ($request->amount < $setting->min_staking) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Minimum staking is ' . $setting->min_staking
                ], 422);
            }

            if ($request->amount > $setting->max_staking) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Maximum staking is ' . $setting->max_staking
                ], 422);
            }

            $walletService = new WalletService();

            if (!$walletService->hasBalance($user->id, 'BMIND', $request->amount)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Insufficient BMIND balance'
                ], 422);
            }


            $profitPercent = 0;

            $level1Percent = 0;
            $level2Percent = 0;
            $level3Percent = 0;

            switch ((int) $request->duration) {

                case 180:
                    $profitPercent = $setting->days_180;

                    $level1Percent = $setting->days_180_af;
                    $level2Percent = $setting->days_180_af2;
                    $level3Percent = $setting->days_180_af3;
                    break;

                case 365:
                    $profitPercent = $setting->days_365;

                    $level1Percent = $setting->days_365_af;
                    $level2Percent = $setting->days_365_af2;
                    $level3Percent = $setting->days_365_af3;
                    break;

                case 730:
                    $profitPercent = $setting->days_730;

                    $level1Percent = $setting->days_730_af;
                    $level2Percent = $setting->days_730_af2;
                    $level3Percent = $setting->days_730_af3;
                    break;

                case 1825:
                    $profitPercent = $setting->days_1825;

                    $level1Percent = $setting->days_1825_af;
                    $level2Percent = $setting->days_1825_af2;
                    $level3Percent = $setting->days_1825_af3;
                    break;
            }


            $dailyProfit = (($request->amount * $profitPercent) / 100) / 365;
            $totalProfit = $dailyProfit * $request->duration;


            Transaction::create([
                'user_id'     => $user->id,
                'wallet'      => 'BMIND',
                'amount'      => -$request->amount,
                'method'      => 'BMIND Staking',
                'type'        => 'Debit',
                'status'      => 'Approved',
                'description' => $request->amount . ' BMIND staking purchase',
            ]);


            $staking = PurchaseStaking::create([
                'user_id'           => $user->id,
                'wallet'            => 'BMIND',
                'amount'            => $request->amount,
                'duration'          => $request->duration,
                'received_days'     => 0,
                'apy_value'         => $profitPercent,
                'total_value'       => $totalProfit,
                'daily'             => $dailyProfit,
                'seller_bonus_rate' => $setting->seller_bonus ?? 5,
                'status'            => 1,
            ]);



            // Affiliate Level 1

            $level1 = $user->sponsor_id
                ? User::find($user->sponsor_id)
                : null;

            if ($level1) {

                $bonus = ($request->amount * $level1Percent) / 100;

                Transaction::create([
                    'user_id'       => $level1->id,
                    'wallet'        => 'BMIND',
                    'amount'        => $bonus,
                    'method'        => 'Affiliate Bonus',
                    'type'          => 'Credit',
                    'status'        => 'Approved',
                    'received_from' => $user->id,
                    'description'   => 'Level 1 affiliate bonus from ' . $user->user_name,
                ]);
            }

            // Affiliate Level 2

            $level2 = ($level1 && $level1->sponsor_id)
                ? User::find($level1->sponsor_id)
                : null;

            if ($level2) {

                $bonus = ($request->amount * $level2Percent) / 100;

                Transaction::create([
                    'user_id'       => $level2->id,
                    'wallet'        => 'BMIND',
                    'amount'        => $bonus,
                    'method'        => 'Affiliate Bonus',
                    'type'          => 'Credit',
                    'status'        => 'Approved',
                    'received_from' => $user->id,
                    'description'   => 'Level 2 affiliate bonus from ' . $user->user_name,
                ]);
            }

            // Affiliate Level 3

            $level3 = ($level2 && $level2->sponsor_id)
                ? User::find($level2->sponsor_id)
                : null;

            if ($level3) {

                $bonus = ($request->amount * $level3Percent) / 100;

                Transaction::create([
                    'user_id'       => $level3->id,
                    'wallet'        => 'BMIND',
                    'amount'        => $bonus,
                    'method'        => 'Affiliate Bonus',
                    'type'          => 'Credit',
                    'status'        => 'Approved',
                    'received_from' => $user->id,
                    'description'   => 'Level 3 affiliate bonus from ' . $user->user_name,
                ]);
            }

            return response()->json([
                'status'  => true,
                'message' => 'BMIND staking successful',
                'data'    => [
                    'id'            => $staking->id,
                    'amount'        => $staking->amount,
                    'duration'      => $staking->duration,
                    'apy'           => $profitPercent . '%',
                    'daily_profit'  => number_format($dailyProfit, 2),
                    'total_profit'  => number_format($totalProfit, 2),
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
