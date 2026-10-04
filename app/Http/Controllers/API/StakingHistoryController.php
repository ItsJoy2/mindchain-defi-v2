<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BmindStakingSetting;
use App\Models\LiquidityPool;
use App\Models\LiquiditySetting;
use App\Models\MindStakingSetting;
use App\Models\MusdStakingSetting;
use App\Models\PurchaseStaking;
use Illuminate\Http\Request;

class StakingHistoryController extends Controller
{
    public function index()
{
    try {

        $user = auth()->user();

        $bmindSetting = BmindStakingSetting::first();
        $mindSetting  = MindStakingSetting::first();
        $musdSetting  = MusdStakingSetting::first();
        $liquiditySetting = LiquiditySetting::first();

        if (!$bmindSetting || !$mindSetting || !$musdSetting || !$liquiditySetting) {
            return response()->json([
                'status' => false,
                'message' => 'Staking or Liquidity settings not found',
                'data' => null
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | BMIND DATA
        |--------------------------------------------------------------------------
        */

        $bmindStaked = 0;
        $bmindRewards = 0;

        if ($user) {

            $bmindStaked = PurchaseStaking::where('user_id', $user->id)
                ->where('wallet', 'BMIND')
                ->where('status', 1)
                ->sum('amount');

            $bmindRewards = PurchaseStaking::where('user_id', $user->id)
                ->where('wallet', 'BMIND')
                ->where('status', 1)
                ->sum('total_value');
        }

        $bmindNetworkApr = number_format(
            (
                $bmindSetting->days_180 +
                $bmindSetting->days_365 +
                $bmindSetting->days_730 +
                $bmindSetting->days_1825
            ) / 4,
            2
        );

        $bmindPlans = [
            [
                'title' => '180 Days',
                'days'  => 180,
                'apy'   => number_format($bmindSetting->days_180, 2)
            ],
            [
                'title' => '365 Days',
                'days'  => 365,
                'apy'   => number_format($bmindSetting->days_365, 2)
            ],
            [
                'title' => '730 Days',
                'days'  => 730,
                'apy'   => number_format($bmindSetting->days_730, 2)
            ],
            [
                'title' => '1825 Days',
                'days'  => 1825,
                'apy'   => number_format($bmindSetting->days_1825, 2)
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | MIND DATA
        |--------------------------------------------------------------------------
        */

        $mindStaked = 0;
        $mindRewards = 0;

        if ($user) {

            $mindStaked = PurchaseStaking::where('user_id', $user->id)
                ->where('wallet', 'MIND')
                ->where('status', 1)
                ->sum('amount');

            $mindRewards = PurchaseStaking::where('user_id', $user->id)
                ->where('wallet', 'MIND')
                ->where('status', 1)
                ->sum('total_value');
        }

        $mindNetworkApr = number_format(
            (
                $mindSetting->days_180 +
                $mindSetting->days_365 +
                $mindSetting->days_730 +
                $mindSetting->days_1825
            ) / 4,
            2
        );

        $mindPlans = [
            [
                'title' => '180 Days',
                'days'  => 180,
                'apy'   => number_format($mindSetting->days_180, 2)
            ],
            [
                'title' => '365 Days',
                'days'  => 365,
                'apy'   => number_format($mindSetting->days_365, 2)
            ],
            [
                'title' => '730 Days',
                'days'  => 730,
                'apy'   => number_format($mindSetting->days_730, 2)
            ],
            [
                'title' => '1825 Days',
                'days'  => 1825,
                'apy'   => number_format($mindSetting->days_1825, 2)
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | MUSD DATA
        |--------------------------------------------------------------------------
        */

        $musdStaked = 0;
        $musdRewards = 0;

        if ($user) {

            $musdStaked = PurchaseStaking::where('user_id', $user->id)
                ->where('wallet', 'MUSD')
                ->where('status', 1)
                ->sum('amount');

            $musdRewards = PurchaseStaking::where('user_id', $user->id)
                ->where('wallet', 'MUSD')
                ->where('status', 1)
                ->sum('total_value');
        }

        $musdNetworkApr = number_format(
            (
                $musdSetting->days_365 +
                $musdSetting->days_730 +
                $musdSetting->days_1825
            ) / 3,
            2
        );

        $musdPlans = [
            [
                'title' => '365 Days',
                'days'  => 365,
                'apy'   => number_format($musdSetting->days_365, 2)
            ],
            [
                'title' => '730 Days',
                'days'  => 730,
                'apy'   => number_format($musdSetting->days_730, 2)
            ],
            [
                'title' => '1825 Days',
                'days'  => 1825,
                'apy'   => number_format($musdSetting->days_1825, 2)
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | LIQUIDITY POOL DATA
        |--------------------------------------------------------------------------
        */

        $liquidityInvested = 0;
        $liquidityReward = 0;
        // $liquidityTotalReturn = 0;
        // $liquidityActiveInvestments = 0;

        if ($user) {

            $liquidityQuery = LiquidityPool::where('user_id', $user->id)
                ->where('status', 'Active');

            $liquidityInvested = (clone $liquidityQuery)
                ->sum('invested_amount');

            $liquidityReward = (clone $liquidityQuery)
                ->sum('reward_amount');
        }

        $liquidityTotalReturnPercentage =
            100 + (float) $liquiditySetting->reward_percentage;


        return response()->json([
            'status' => true,
            'message' => 'Staking data retrieved successfully',

            'data' => [

                /*
                |--------------------------------------------------------------------------
                | BMIND
                |--------------------------------------------------------------------------
                */

                'bmind' => [
                    'network_apr'   => $bmindNetworkApr . '%',
                    'your_staked'   => number_format($bmindStaked, 2),
                    'total_rewards' => number_format($bmindRewards, 2),
                    'min_staking'   => $bmindSetting->min_staking,
                    'max_staking'   => $bmindSetting->max_staking,
                    'plans'         => $bmindPlans
                ],

                /*
                |--------------------------------------------------------------------------
                | MIND
                |--------------------------------------------------------------------------
                */

                'mind' => [
                    'network_apr'   => $mindNetworkApr . '%',
                    'your_staked'   => number_format($mindStaked, 2),
                    'total_rewards' => number_format($mindRewards, 2),
                    'min_staking'   => $mindSetting->min_staking,
                    'max_staking'   => $mindSetting->max_staking,
                    'plans'         => $mindPlans
                ],

                /*
                |--------------------------------------------------------------------------
                | MUSD
                |--------------------------------------------------------------------------
                */

                'musd' => [
                    'network_apr'   => $musdNetworkApr . '%',
                    'your_staked'   => number_format($musdStaked, 2),
                    'total_rewards' => number_format($musdRewards, 2),
                    'min_staking'   => $musdSetting->min_staking,
                    'max_staking'   => $musdSetting->max_staking,
                    'plans'         => $musdPlans
                ],

                /*
                |--------------------------------------------------------------------------
                | LIQUIDITY POOL
                |--------------------------------------------------------------------------
                */

                'liquidity' => [
                    'status' => (bool) $liquiditySetting->status,

                    'min_amount' => number_format(
                        $liquiditySetting->min_amount,
                        2,
                        '.',
                        ''
                    ),

                    'reward_percentage' => number_format(
                        $liquiditySetting->reward_percentage,
                        2,
                        '.',
                        ''
                    ),

                    'lock_days' => (int) $liquiditySetting->lock_days,
                ],
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
    public function stakingHistory(Request $request)
    {
        try {

            $user = auth()->user();


            $perPage = (int) $request->get('per_page', 10);
            $wallet  = $request->get('wallet');

            $query = PurchaseStaking::where('user_id', $user->id);

            if ($request->filled('status')) {

                switch (strtolower($request->status)) {

                    case 'Running':
                        $query->where('status', 1);
                        break;

                    case 'Expired':
                        $query->where('status', 0);
                        break;
                }
            }

            if ($wallet) {
                $query->where('wallet', $wallet);
            }

            $histories = $query->select([
                    'id',
                    'wallet',
                    'amount',
                    'duration',
                    'received_days',
                    'daily',
                    'status',
                    'created_at'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'Staking history retrieved successfully',
                'data' => collect($histories->items())->map(function ($row) {

                    return [
                        'id'             => $row->id,
                        'wallet'         => $row->wallet,
                        'amount'         => (float) $row->amount,
                        'duration'       => (int) $row->duration,
                        'received_days'  => (int) $row->received_days,
                        'daily'          => (float) $row->daily,
                        'status'         => $row->status == 1 ? 'Running' : 'Expired',
                        'created_at'     => $row->created_at->format('Y-m-d H:i:s'),
                    ];
                }),

                'pagination' => [
                    'page'     => $histories->currentPage(),
                    'per_page' => $histories->perPage(),
                    'total'    => $histories->total(),
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
