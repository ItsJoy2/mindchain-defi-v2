<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorHistory;
use App\Models\AngelWalletHistory;
use App\Models\BmindStakingHistory;
use App\Models\EliteV2StakingHistory;
use App\Models\MindPurchaseStake;
use App\Models\MindStakingHistory;
use App\Models\MusdStakingHistory;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        try {

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $userId = $user->id;

            // Static Prices
            $mind_price  = 0.40;
            $musd_price  = 0.95;
            $bmind_price = 0.06;
            $usdt_price  = 1;

            // Wallet Amounts
            $mind_wallet = Transaction::where('user_id', $userId)
                ->where('wallet', 'MIND')
                ->whereIn('status', ['Approved', 'Pending'])
                ->whereNotIn('method', ['Kids Program Membership', 'MIND Marge Staking Received'])
                ->sum('amount');

            $bmind_wallet = Transaction::where('user_id', $userId)
                ->where('wallet', 'BMIND')
                ->where('status', 'Approved')
                ->sum('amount');

            $musd_wallet = Transaction::where('user_id', $userId)
                ->where('wallet', 'MUSD')
                ->whereIn('status', ['Approved', 'Pending'])
                ->sum('amount');

            $usdt_wallet = Transaction::where('user_id', $userId)
                ->where('wallet', 'USDT')
                ->where('status', 'Approved')
                ->sum('amount');

            $wallets = [

                'mind_wallet' => [
                    'balance' => number_format($mind_wallet, 2),
                    'value'   => number_format($mind_wallet * $mind_price, 2),
                ],

                'bmind_wallet' => [
                    'balance' => number_format($bmind_wallet, 2),
                    'value'   => number_format($bmind_wallet * $bmind_price, 2),
                ],

                'musd_wallet' => [
                    'balance' => number_format($musd_wallet, 2),
                    'value'   => number_format($musd_wallet * $musd_price, 2),
                ],

                'usdt_wallet' => [
                    'balance' => number_format($usdt_wallet, 2),
                    'value'   => number_format($usdt_wallet * $usdt_price, 2),
                ],

                'mind_staking' => [
                    'balance' => number_format(MindPurchaseStake::where('user_id', $userId)->where('status', 1)->sum('amount'), 2),
                    'value'   => number_format(MindPurchaseStake::where('user_id', $userId)->where('status', 1)->sum('amount') * $mind_price, 2),
                ],

                'bmind_staking' => [
                    'balance' => number_format(BmindStakingHistory::where('user_id', $userId)->sum('amount'), 2),
                    'value'   => number_format(BmindStakingHistory::where('user_id', $userId)->sum('amount') * $bmind_price, 2),
                    ],

                'ambassador_wallet' => [
                    'balance' => number_format(AmbassadorHistory::where('user_id', $userId)->sum('amount'), 2),
                    'value'   => number_format(AmbassadorHistory::where('user_id', $userId)->sum('amount') * $mind_price, 2),
                    ],

                'musd_staking' => [
                    'balance' => number_format(MusdStakingHistory::where('user_id', $userId)->sum('amount'), 2),
                    'value'   => number_format(MusdStakingHistory::where('user_id', $userId)->sum('amount') * $musd_price, 2),
                ],

                'angel_wallet' => [
                    'balance' => number_format(AngelWalletHistory::where('user_id', $userId)->sum('amount'), 2),
                    'value'   => number_format(AngelWalletHistory::where('user_id', $userId)->sum('amount') * $musd_price, 2),
                    ],

                'elite_club' => [
                    'balance' => number_format(Transaction::where('user_id', $userId)->where('wallet', 'USDT')->where('method', 'Buy Elite Membership')->sum(DB::raw('ABS(amount)')), 2),
                    'value'   => number_format(Transaction::where('user_id', $userId)->where('wallet', 'USDT')->where('method', 'Buy Elite Membership')->sum(DB::raw('ABS(amount)')) * $usdt_price, 2),
                    ],

                'elite_Club_v2' =>[
                    'balance' => number_format(EliteV2StakingHistory::where('user_id', $userId)->sum('amount'), 2),
                    'value'   => number_format(EliteV2StakingHistory::where('user_id', $userId)->sum('amount') * $musd_price, 2),
                ],

                'mind_kids' => [
                    'balance' => number_format(Transaction::where('user_id', $userId)->where('method', 'Kids Program Membership')->sum('amount'), 2),
                    'value'   => number_format(Transaction::where('user_id', $userId)->where('method', 'Kids Program Membership')->sum('amount') * $mind_price, 2),
                ],

            ];


            $usdt_history = Transaction::where('user_id', $userId)
                ->where('wallet', 'USDT')
                ->latest()
                ->paginate(10, ['*'], 'usdt_page')
                ->map(function ($item) {

                    return [
                        'txn_id'      => $item->txn_id,
                        'wallet'      => $item->wallet,
                        'method'      => $item->method,
                        'type'        => $item->type,
                        'amount'      => number_format($item->amount, 2),
                        'status'      => $item->status,
                        'description' => $item->description,
                        'date'        => $item->created_at->format('d M Y '),
                    ];
                });

            $musd_history = Transaction::where('user_id', $userId)
                ->where('wallet', 'MUSD')
                ->latest()
                ->paginate(10, ['*'], 'musd_page')
                ->map(function ($item) {

                    return [
                        'txn_id'      => $item->txn_id,
                        'wallet'      => $item->wallet,
                        'method'      => $item->method,
                        'type'        => $item->type,
                        'amount'      => number_format($item->amount, 2),
                        'status'      => $item->status,
                        'description' => $item->description,
                        'date'        => $item->created_at->format('d M Y'),
                    ];
                });


            return response()->json([
                'status' => true,
                'message' => 'Dashboard data fetched successfully',

                'data' => [
                    'wallets'      => $wallets,
                    // 'prices' => [
                    //     'mind_price'  => $mind_price,
                    //     'musd_price'  => $musd_price,
                    //     'bmind_price' => $bmind_price,
                    //     'usdt_price'  => $usdt_price,
                    // ],
                    'usdt_history' => $usdt_history,
                    'musd_history' => $musd_history,
                ]

            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
